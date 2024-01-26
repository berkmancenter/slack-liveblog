<?php

namespace SlackLiveblog;

/**
 * Class FrontActions
 *
 * Handles front-end actions for the plugin.
 *
 * @package SlackLiveblog
 */
class FrontActions {
  public function __construct() {
    $this->init_actions();
  }

  /**
   * Sets up actions.
   *
   * @return void
   */
  private function init_actions() {
    FrontCore::$channels->publish_delayed_messages();
    $this->get_channel_messages();
  }

  /**
   * Fetches and outputs channel messages based on input parameters.
   *
   * @return void
   */
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

    $channel_messages = [];

    $new_messages = FrontCore::$channels->get_channel_messages($channel->id, $from_time_unix);
    $channel_messages['new'] = array_map([$this, 'formatMessage'], $new_messages);

    if ($from_time_unix) {
      $updated_messages = FrontCore::$channels->get_channel_messages($channel->id, false, $from_time_unix);
      $channel_messages['updated'] = array_map([$this, 'formatMessage'], $updated_messages);

      $deleted_messages = FrontCore::$channels->get_channel_messages($channel->id, false, false, $from_time_unix);
      $channel_messages['deleted'] = array_map([$this, 'formatMessage'], $deleted_messages);
    } else {
      $channel_messages['updated'] = [];
      $channel_messages['deleted'] = [];
    }

    echo json_encode($channel_messages);
    die();
  }

  /**
   * Formats a message for output.
   *
   * @param object $message Message object.
   * @return array Formatted message array.
   */
  private function formatMessage($message) {
    return [
      'id' => $message->id,
      'body' => $message->message,
      'author' => $message->name,
      'created_at' => $message->remote_created_at,
      'reactions' => json_decode($message->reactions),
    ];
  }
}
