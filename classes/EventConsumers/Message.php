<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Helpers;

class Message extends Consumer {
  public function consume(): array {
    $local_channel_id = FrontCore::$channels->get_channel($this->slack_channel_id, 'slack_id')->id;
    $slack_user_id = $this->data['event']['user'];
    $slack_message_id = $this->data['event']['client_msg_id'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id);

    $message_text = $this->get_message_from_blocks($this->data['event']['blocks']);
    $message_text = $this->decorate_message($message_text);

    $local_message = FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_id' => $author->id,
      'slack_id' => $slack_message_id
    ]);

    $clients_message = [
      'action' => 'message_new',
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_name' => $author->name,
      'created_at' => Helpers::i()->get_parsed_timezoned_date($local_message->created_at),
      'id' => $local_message->id
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
