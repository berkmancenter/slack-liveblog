<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class Message extends Consumer {
  public function consume() {
    $local_channel = FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id]);
    $slack_user_id = $this->data['event']['user'];
    $slack_message_id = $this->data['event']['client_msg_id'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id, $local_channel->workspace_id);

    $message_text = $this->get_message_text($this->data['event']);

    if (Db::i()->get_row('channel_messages', ['id'], ['slack_id' => $slack_message_id])) {
      return [];
    }

    $unix_remote_message_timestamp = $this->data['event']['ts'];

    $local_channel_data = [
      'channel_id' => $local_channel->id,
      'message' => $message_text,
      'author_id' => $author->id,
      'slack_id' => $slack_message_id,
      'remote_created_at' => "SQL_FUNC:DATE_FORMAT(FROM_UNIXTIME({$unix_remote_message_timestamp}), '%Y-%m-%d %H:%i:%s.%f')",
    ];

    $delay = intval($local_channel->delay);
    if ($delay && $delay > 0) {
      $local_channel_data['published'] = false;
      $publish_at = $unix_remote_message_timestamp + $delay;
      $local_channel_data['publish_at'] = "SQL_FUNC:DATE_FORMAT(FROM_UNIXTIME({$publish_at}), '%Y-%m-%d %H:%i:%s.%f')";
    }

    $local_message = FrontCore::$channels->create_local_message($local_channel_data);

    $clients_message = [
      'action' => 'message_new',
      'channel_id' => $local_channel->uuid,
      'body' => $message_text,
      'author' => $author->name,
      'created_at' => $local_message->created_at,
      'id' => $local_message->id,
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
