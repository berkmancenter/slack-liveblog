<?php

namespace SlackLiveblog;

require '../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;
use Symfony\Component\Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = new Dotenv();
$dotenv->load('../.env');

/**
 * Class WebsocketServer
 *
 * WebSocket server class for handling client connections and messages.
 *
 * @package SlackLiveblog
 */
class WebsocketServer implements MessageComponentInterface {
  /** @var \SplObjectStorage Collection to store connected clients. */
  protected $clients;

  public function __construct() {
    $this->clients = new \SplObjectStorage;
  }

  /**
   * Handles new client connection.
   * 
   * @param ConnectionInterface $conn Connection object representing the client.
   * @return void
   */
  public function onOpen(ConnectionInterface $conn) {
    $http_params = [];
    parse_str($conn->httpRequest->getUri()->getQuery(), $http_params);

    $conn->channel_id = $http_params['channel_id'] ?? null;

    // Store the new connection to send messages to later
    $this->clients->attach($conn);
  }

  /**
   * Handles incoming messages.
   * 
   * @param ConnectionInterface $from Sender's connection object.
   * @param string $msg  Message sent by client.
   * @return void
   */
  public function onMessage(ConnectionInterface $from, $msg) {
    $msg_decoded = json_decode($msg);

    foreach ($this->clients as $client) {
      if ($from !== $client && $client->channel_id === $msg_decoded->channel_id) {
        // The sender is not the receiver, send to each client connected
        $client->send($msg);
      }
    }
  }

  /**
   * Handles connection closure.
   * 
   * @param ConnectionInterface $conn Connection object of client.
   * @return void
   */
  public function onClose(ConnectionInterface $conn) {
    // The connection is closed, remove it, as we can no longer send it messages
    $this->clients->detach($conn);
  }

  /**
   * Handles connection errors.
   * 
   * @param ConnectionInterface $conn Connection object of client.
   * @param \Exception $e Exception thrown.
   * @return void
   */
  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "Error occurred: {$e->getMessage()}\n";
    $conn->close();
  }
}

// Set up and run a WebSocket server
$host = $_ENV['SLACK_LIVEBLOG_WS_SERVER_HOST'];
$port = $_ENV['SLACK_LIVEBLOG_WS_SERVER_PORT'] ?? 8080;
$app = new App($host, $port, '0.0.0.0');
$app->route('/ws', new WebsocketServer, ['*']);
$app->run();
