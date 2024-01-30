<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class Message extends Consumer {
  /**
   * Consumes a message and returns a formatted message body.
   *
   * @return array
   */
  public function consume() {
    $event_data = $this->data['event'];

    $local_channel = $this->get_local_channel();
    $author = $this->get_author($local_channel->workspace_id);

    if ($this->message_exists($event_data['client_msg_id'])) {
      return [];
    }

    $local_message = $this->create_local_message($local_channel, $author, $event_data);
    $clients_message = $this->prepare_clients_message($local_channel, $local_message, $author);

    return [
      'message_body' => $clients_message,
    ];
  }

  private function get_local_channel() {
    return FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id]);
  }

  private function get_author($workspace_id) {
    return FrontCore::$channels->get_or_create_author_by_slack_id($this->data['event']['user'], $workspace_id);
  }

  private function message_exists($slack_message_id) {
    return Db::i()->get_row('channel_messages', ['id'], ['slack_id' => $slack_message_id]);
  }

  private function create_local_message($local_channel, $author, $event_data) {
    $message_data = [
      'channel_id' => $local_channel->id,
      'message' => $this->get_message_text($event_data),
      'author_id' => $author->id,
      'slack_id' => $event_data['client_msg_id'],
      'remote_created_at' => $this->format_unix_time($event_data['ts']),
    ];

    if (isset($event_data['thread_ts'])) {
      $thread_ts = $event_data['thread_ts'];
      $rounded_timestamp_microsecs_to_milisecs = "SQL_FUNC:CONCAT(DATE_FORMAT(FROM_UNIXTIME(" . $thread_ts . "), '%Y-%m-%d %H:%i:%s'), '.', LPAD(ROUND(MICROSECOND(FROM_UNIXTIME(" . $thread_ts . ")) / 1000), 3, '0'))";
      $local_thread_message = FrontCore::$channels->get_message($rounded_timestamp_microsecs_to_milisecs, 'remote_created_at');

      if (!$local_thread_message) {
        return false;
      }

      $message_data['parent_id'] = $local_thread_message->id;
    }

    $delay = intval($local_channel->delay);
    if ($delay && $delay > 0) {
      $message_data['published'] = false;
      $message_data['publish_at'] = $this->format_unix_time($event_data['ts'] + $delay);
    }
    
    return FrontCore::$channels->create_local_message($message_data);
  }

  private function prepare_clients_message($local_channel, $local_message, $author) {
    return [
      'action' => 'message_new',
      'channel_id' => $local_channel->uuid,
      'body' => $local_message->message,
      'author' => $author->name,
      'created_at' => $local_message->created_at,
      'id' => $local_message->id,
      'parent_id' => $local_message->parent_id,
    ];
  }
}
