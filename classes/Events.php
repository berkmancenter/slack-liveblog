<?php

namespace SlackLiveblog;

class Events {
  private $SIGNING_SECRET;

  public function __construct() {
    $this->SIGNING_SECRET = PluginSettings::i()->get('slack_liveblog_checkbox_field_api_signing_secret');

    add_action('init', [$this, 'slack_liveblog_events_init']);
  }

  public function slack_liveblog_events_init() {
    if (strpos($_SERVER[ 'REQUEST_URI' ], '/slack_liveblog_events') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->incoming_data = json_decode(file_get_contents('php://input'), true);

      if ($this->incoming_data['type'] === 'url_verification') {
        echo $this->incoming_data['challenge'];
        die();
      } else {
        $this->handle_event();
      }
    }
  }

  private function handle_event() {
    $channel_id = $this->incoming_data['event']['channel'];
    $instance_channels = FrontCore::$channels->get_channels_field('slack_id');

    if ($this->incoming_data['token'] !== $this->SIGNING_SECRET) {
      $this->respond_event();
    }

    if ($this->incoming_data['event']['type'] !== 'message' || isset($this->incoming_data['event']['subtype'])) {
      $this->respond_event();
    }

    if (in_array($channel_id, $instance_channels) === false) {
      $this->respond_event();
    }

    $local_channel_id = FrontCore::$channels->get_channel_by_slack_id($channel_id)->id;

    $slack_user_id = $this->incoming_data['event']['user'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id);
    $message_text = $this->decorate_message($this->incoming_data['event']['text']);

    $local_message = FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_id' => $author->id
    ]);

    $ws_message = [
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_name' => $author->name,
      'created_at' => $local_message->created_at
    ];

    $react_connector = new \React\Socket\Connector([
      'tls' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ],
    ]);
    $loop = \React\EventLoop\Loop::get();
    $connector = new \Ratchet\Client\Connector($loop, $react_connector);
    $connector($_ENV['WS_SERVER_CLIENT_URL'] . "?channel_id=$local_channel_id")->then(function($conn) use ($ws_message) {
      try {
        $conn->send(json_encode($ws_message));
      } catch (\Exception $e) {
        throw new Exception('Could send data to WS server.');
      } finally {
        $conn->close(); 
      }
    }, function ($e) {
      throw new Exception('Could not connect to WS server.');
    });

    $this->respond_event();
  }

  private function decorate_message($message_text) {
    return nl2br($message_text);
  }

  private function respond_event() {
    echo 'ok';
    die();
  }
}
