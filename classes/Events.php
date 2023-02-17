<?php

namespace SlackLiveblog;

use SlackLiveblog\EventConsumers\MessageNewConsumer;

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

    if (in_array($channel_id, $instance_channels) === false) {
      $this->respond();
    }

    if ($this->incoming_data['event']['type'] === 'message' && isset($this->incoming_data['event']['subtype']) === false) {
      $message_to_broadcast = (new EventConsumers\MessageNewConsumer($this->incoming_data, $channel_id))->consume();
    }

    $this->broadcast_message($message_to_broadcast['message_body']);

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

  private function is_valid_request() {
    $signature = $_SERVER['HTTP_X_SLACK_SIGNATURE'];
    list($version, $hash) = explode('=', $signature, 2);
    $base_string = sprintf('%s:%s:%s', $version, time(), $this->raw_incoming_data);
    $computed_hash = hash_hmac('sha256', $base_string, $this->signing_secret);

    return hash_equals($hash, $computed_hash);
  }

  private function camelize($string) {
    return str_replace('-', '', ucwords($string, '-'));
  }
}
