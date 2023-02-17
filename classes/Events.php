<?php

namespace SlackLiveblog;

class Events {
  private string $signing_secret;
  private string $raw_incoming_data;
  private array $incoming_data;

  public function __construct() {
    $this->signing_secret = PluginSettings::i()->get('slack_liveblog_checkbox_field_api_signing_secret');

    add_action('init', [$this, 'slack_liveblog_events_init']);
  }

  public function slack_liveblog_events_init() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    if (strpos($_SERVER['REQUEST_URI'], '/slack_liveblog_events') === false) {
      return;
    }

    $this->raw_incoming_data = file_get_contents('php://input');
    $this->incoming_data = json_decode($this->raw_incoming_data, true);

    if ($this->incoming_data['type'] === 'url_verification') {
      echo $this->incoming_data['challenge'];
      die();
    } else {
      $this->handle_event();
    }
  }

  private function handle_event() {
    $channel_id = $this->incoming_data['event']['channel'];
    $instance_channels = FrontCore::$channels->get_channels_field('slack_id');

    if ($this->is_valid_request() === false) {
      $this->respond('Invalid request signature');
    }

    if ($this->incoming_data['event']['type'] !== 'message' || isset($this->incoming_data['event']['subtype'])) {
      $this->respond();
    }

    if (in_array($channel_id, $instance_channels) === false) {
      $this->respond();
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

    $this->broadcast_message($local_channel_id, $ws_message);

    $this->respond();
  }

  private function decorate_message($message_text) {
    return nl2br($message_text);
  }

  private function respond($message = 'ok') {
    echo $message;
    die();
  }

  private function broadcast_message($channel_id, $message) {
    $react_connector = new \React\Socket\Connector([
      'tls' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ],
    ]);
    $loop = \React\EventLoop\Loop::get();
    $connector = new \Ratchet\Client\Connector($loop, $react_connector);
    $connector($_ENV['WS_SERVER_CLIENT_URL'] . "?channel_id=$channel_id")->then(function($conn) use ($message) {
      try {
        $conn->send(json_encode($message));
      } catch (\Exception $e) {
        throw new Exception('Could send data to WS server.');
      } finally {
        $conn->close(); 
      }
    }, function ($e) {
      throw new Exception('Could not connect to WS server.');
    });
  }

  private function is_valid_request() {
    $signature = $_SERVER['HTTP_X_SLACK_SIGNATURE'];
    list($version, $hash) = explode('=', $signature, 2);
    $base_string = sprintf('%s:%s:%s', $version, time(), $this->raw_incoming_data);
    $computed_hash = hash_hmac('sha256', $base_string, $this->signing_secret);

    return hash_equals($hash, $computed_hash);
  }
}
