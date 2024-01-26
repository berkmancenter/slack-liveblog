<?php

namespace SlackLiveblog;

use SlackLiveblog\EventConsumers;
use React\Socket\Connector;
use React\EventLoop\Loop;
use Ratchet\Client\Connector as WSConnector;

/**
 * The Events class handles incoming Slack events for the live blog platform.
 *
 * This class is responsible for processing incoming HTTP requests that
 * contain Slack event data. It distinguishes between different types of
 * events, processes them accordingly, and optionally broadcasts certain
 * messages through websockets.
 *
 * @package SlackLiveblog
 */
class Events {
  /** @var string Raw incoming event data from Slack. */
  private string $raw_incoming_data;

  /** @var array Decoded representation of the raw incoming event data from Slack. */
  private array $incoming_data;

  public function __construct() {
    add_action('init', [$this, 'slack_liveblog_events_init']);
  }

  /**
   * Initializes Slack event processing.
   *
   * @return void
   */
  public function slack_liveblog_events_init() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    if (strpos($_SERVER['REQUEST_URI'], '/slack_liveblog_events') === false) {
      return;
    }

    $this->set_incoming_data();
    $this->log_data_if_debug();
    $this->handle_event();
  }

  /**
   * Set raw and decoded incoming data.
   *
   * @return void
   */
  private function set_incoming_data() {
    $this->raw_incoming_data = file_get_contents('php://input');
    $this->incoming_data = json_decode($this->raw_incoming_data, true);
  }

  /**
   * Logs raw data if in debug mode.
   *
   * @return void
   */
  private function log_data_if_debug() {
    if ($_ENV['SLACK_LIVEBLOG_DEBUG'] === 'true') {
      error_log($this->raw_incoming_data);
    }
  }

  /**
   * Handles incoming Slack events.
   *
   * @return void
   */
  private function handle_event() {
    $channel_id = null;

    if ($this->incoming_data['type'] === 'url_verification') {
      echo $this->incoming_data['challenge'];
      exit;
    }

    if (isset($this->incoming_data['event']['channel'])) {
      $channel_id = $this->incoming_data['event']['channel'];
    }

    if (isset($this->incoming_data['event']['item']['channel'])) {
      $channel_id = $this->incoming_data['event']['item']['channel'];
    }

    if (!in_array($channel_id, FrontCore::$channels->get_open_channels_slack_ids())) {
      $this->respond();
    }

    $channel = FrontCore::$channels->get_channel(['slack_id' => $channel_id]);
    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $channel->workspace_id]);

    if (!$this->is_valid_request($workspace->verification_token)) {
      $this->respond('Invalid request signature');
    }

    $consumer_class = $this->get_consumer_class_name();
    if (class_exists($consumer_class)) {
      $consumed = (new $consumer_class($this->incoming_data, $channel_id))->consume();
      if ($_ENV['SLACK_LIVEBLOG_USE_WEBSOCKETS'] === 'true' && isset($consumed['message_body'])) {
        $this->broadcast_message($consumed['message_body']);
      }
    }

    $this->respond();
  }

  /**
   * Sends a response to the incoming event.
   *
   * @param string $message Response message.
   */
  private function respond($message = 'ok') {
    echo $message;
    exit;
  }

  /**
   * Converts an incoming event type to its corresponding consumer class name.
   *
   * @return string Class name.
   */
  private function get_consumer_class_name(): string {
    $name = $this->incoming_data['event']['type'];

    if (isset($this->incoming_data['event']['subtype'])) {
      $name .= "_{$this->incoming_data['event']['subtype']}";
    }

    $class_name = str_replace('_', '', ucwords($name, '_'));

    return "SlackLiveblog\EventConsumers\\$class_name";
  }

  /**
   * Broadcasts a message using websockets.
   * 
   * @param string $message Message to broadcast.
   */
  private function broadcast_message($message) {
    $react_connector = new Connector([
      'tls' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
      ],
    ]);
    $loop = Loop::get();
    $connector = new WSConnector($loop, $react_connector);

    $connector($_ENV['SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL'])->then(function($conn) use ($message) {
      try {
        $conn->send(json_encode($message));
      } catch (\Exception $e) {
        throw new \Exception('Could send data to WS server.');
      } finally {
        $conn->close(); 
      }
    }, function ($e) {
      throw new \Exception('Could not connect to WS server.');
    });
  }

  /**
   * Validates the incoming request.
   *
   * @param string $signing_secret Signing secret for validation.
   * @return bool True if valid, false otherwise.
   */
  private function is_valid_request($signing_secret): bool {
    return $signing_secret === $this->incoming_data['token'];
  }
}
