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

    $channel_uuid = preg_replace('/[^a-zA-Z0-9\-]/', '', filter_input(INPUT_GET, 'channel_id'));
    $channel = FrontCore::$channels->get_channel(['uuid' => $channel_uuid]);

    if (!$channel) {
      echo json_encode(['Channel not found']);
      die();
    }

    $from_time_js = preg_replace('/[^0-9]/', '', filter_input(INPUT_GET, 'from') ?? '');
    $from_time_unix = $from_time_js ? floor(intval($from_time_js) / 1000) : '';
    $channel_messages = array_map(static function ($message) {
      return [
          'id' => $message->id,
          'body' => $message->message,
          'author' => $message->name,
          'created_at' => $message->created_at,
      ];
    }, FrontCore::$channels->get_channel_messages($channel->id, $from_time_unix));

    echo json_encode($channel_messages);
    die();
  }
}
