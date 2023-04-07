<?php

namespace SlackLiveblog;

class FrontActions {
  public function __construct() {
    $this->init_actions();
  }

  private function init_actions() {
    $this->get_channel_messages();
  }

  private function get_channel_messages() {
    if (($_GET['action'] ?? '') !== 'slack_liveblog_get_channel_messages' || !isset($_GET['channel_id'])) {
      return;
    }

    $channel_uuid = filter_input(INPUT_GET, 'channel_id', FILTER_SANITIZE_STRING);
    $channel = FrontCore::$channels->get_channel($channel_uuid, 'uuid');

    if (!$channel) {
      echo json_encode(['Channel not found']);
      die();
    }

    $channel_messages = FrontCore::$channels->get_channel_messages($channel->id);
    $channel_messages = array_map(function ($message) {
      return [
        'id' => $message->id,
        'body' => $message->message,
        'author' => $message->name,
        'created_at' => $message->created_at
      ];
    }, $channel_messages);

    echo json_encode($channel_messages);
    die();
  }
}
