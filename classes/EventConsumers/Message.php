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

    $message_text = $this->get_message_from_blocks($this->data['event']['blocks']);
    $message_text = $this->decorate_message($message_text);

    if (Db::i()->get_row('channel_messages', ['id'], ['slack_id' => $slack_message_id])) {
      return [];
    }

    $local_message = FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel->id,
      'message' => $message_text,
      'author_id' => $author->id,
      'slack_id' => $slack_message_id
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
