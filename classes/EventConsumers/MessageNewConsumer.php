<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;

class MessageNewConsumer extends Consumer {
  public function consume(): array {
    $local_channel_id = FrontCore::$channels->get_channel_by_slack_id($this->channel_id)->id;
    $slack_user_id = $this->data['event']['user'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id);
    $message_text = $this->decorate_message($this->data['event']['text']);

    $local_message = FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_id' => $author->id
    ]);

    $clients_message = [
      'channel_id' => $local_channel_id,
      'message' => $message_text,
      'author_name' => $author->name,
      'created_at' => $local_message->created_at
    ];

    return [
      'message_body' => $clients_message
    ];
  }

  private function decorate_message($message_text) {
    return nl2br($message_text);
  }
}
