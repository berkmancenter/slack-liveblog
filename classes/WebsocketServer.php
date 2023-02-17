<?php

namespace SlackLiveblog;

require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/../.env');

class WebsocketServer implements MessageComponentInterface {
  protected $clients;

  public function __construct() {
    $this->clients = new \SplObjectStorage;
  }

  public function onOpen(ConnectionInterface $conn) {
    $http_params = [];
    parse_str($conn->httpRequest->getUri()->getQuery(), $http_params);

    $conn->channel_id = $http_params['channel_id'];

    // Store the new connection to send messages to later
    $this->clients->attach($conn);

    echo "New connection! ({$conn->resourceId})\n";
  }

  public function onMessage(ConnectionInterface $from, $msg) {
    $numRecv = count($this->clients) - 1;
    echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
      , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

    $msg_decoded = json_decode($msg);

    foreach ($this->clients as $client) {
      if ($from !== $client && $client->channel_id === $msg_decoded->channel_id) {
        // The sender is not the receiver, send to each client connected
        $client->send($msg);
      }
    }
  }

  public function onClose(ConnectionInterface $conn) {
    // The connection is closed, remove it, as we can no longer send it messages
    $this->clients->detach($conn);

    echo "Connection {$conn->resourceId} has disconnected\n";
  }

  public function onError(ConnectionInterface $conn, \Exception $e) {
    echo "An error has occurred: {$e->getMessage()}\n";

    $conn->close();
  }
}

$app = new App($_ENV['WS_SERVER_HOST'], $_ENV['WS_SERVER_PORT'] ??= 8080, '0.0.0.0');
$app->route('/ws', new WebsocketServer, array('*'));
$app->run();
