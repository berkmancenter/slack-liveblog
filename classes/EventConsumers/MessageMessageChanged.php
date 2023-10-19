<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;

class MessageMessageChanged extends Consumer {
  public function consume(): array {
    $slack_message_id = $this->data['event']['message']['client_msg_id'];
    $local_message_id = FrontCore::$channels->get_message($slack_message_id, 'slack_id')->id;
    $local_channel_uuid = FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id])->uuid;

    $message_text = $this->get_message_text($this->data['event']['message']);

    FrontCore::$channels->update_local_message([
      'message' => $message_text
    ], [
      'id' => $local_message_id
    ]);

    $clients_message = [
      'action' => 'message_changed',
      'channel_id' => $local_channel_uuid,
      'message' => $message_text,
      'id' => $local_message_id
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
