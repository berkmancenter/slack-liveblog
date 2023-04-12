<?php

namespace SlackLiveblog;

use SlackLiveblog\EventConsumers;

class Events {
  private string $raw_incoming_data;
  private array $incoming_data;

  public function __construct() {
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

    if (isset($_ENV['SLACK_LIVEBLOG_DEBUG']) && $_ENV['SLACK_LIVEBLOG_DEBUG'] === 'true') {
      error_log($this->raw_incoming_data);
    }

    if ($this->incoming_data['type'] === 'url_verification') {
      echo $this->incoming_data['challenge'];
      die();
    } else {
      $this->handle_event();
    }
  }

  private function handle_event() {
    $channel_id = $this->incoming_data['event']['channel'];

    $instance_channels = FrontCore::$channels->get_open_channels_slack_ids();

    if (in_array($channel_id, $instance_channels) === false) {
      $this->respond();
    }

    $channel = FrontCore::$channels->get_channel(['slack_id' => $channel_id]);
    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $channel->workspace_id]);

    if ($this->is_valid_request($workspace->verification_token) === false) {
      $this->respond('Invalid request signature');
    }

    $consumer_name = $this->incoming_data['event']['type'];
    if (isset($this->incoming_data['event']['subtype'])) {
      $consumer_name .= "_{$this->incoming_data['event']['subtype']}";
    }
    $consumer_class_name = $this->camelize($consumer_name);
    $consumer_class_name = "SlackLiveblog\EventConsumers\\$consumer_class_name";

    if (class_exists($consumer_class_name)) {
      $consumed = (new $consumer_class_name($this->incoming_data, $channel_id))->consume();
    }

    if (@$_ENV['SLACK_LIVEBLOG_USE_WEBSOCKETS'] === 'true' && isset($consumed['message_body']) === true) {
      $this->broadcast_message($consumed['message_body']);
    }

    $this->respond();
  }

  private function respond($message = 'ok') {
    echo $message;
    die();
  }

  private function broadcast_message($message) {
    $react_connector = new \React\Socket\Connector([
      'tls' => [
        'verify_peer' => false,
        'verify_peer_name' => false
      ],
    ]);
    $loop = \React\EventLoop\Loop::get();
    $connector = new \Ratchet\Client\Connector($loop, $react_connector);
    $connector($_ENV['WS_SERVER_CLIENT_URL'])->then(function($conn) use ($message) {
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

  private function is_valid_request($signing_secret) {
    return $signing_secret === $this->incoming_data['token'];
  }

  private function camelize($string) {
    return str_replace('_', '', ucwords($string, '_'));
  }
}
