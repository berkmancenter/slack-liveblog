<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class Message extends Consumer {
  public function consume(): array {
    $local_channel = FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id]);
    $slack_user_id = $this->data['event']['user'];
    $slack_message_id = $this->data['event']['client_msg_id'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id, $local_channel->workspace_id);

    $message_text = $this->get_message_text($this->data['event']);

    if (Db::i()->get_row('channel_messages', ['id'], ['slack_id' => $slack_message_id])) {
      return [];
    }

    $js_timestamp = $this->data['event']['ts'];
    $unix_timestamp = floor($js_timestamp);
    $date_string = date('Y-m-d H:i:s.', $unix_timestamp);
    $decimal_portion = sprintf('%03d', ($js_timestamp - $unix_timestamp) * 1000);
    $timestamp = $date_string . $decimal_portion;

    $local_message = FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel->id,
      'message' => $message_text,
      'author_id' => $author->id,
      'slack_id' => $slack_message_id,
      'created_at' => $timestamp
    ]);

    $clients_message = [
      'action' => 'message_new',
      'channel_id' => $local_channel->uuid,
      'body' => $message_text,
      'author' => $author->name,
      'created_at' => $local_message->created_at,
      'id' => $local_message->id
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
