<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class MessageMessageDeleted extends Consumer {
  public function consume(): array {
    $slack_message_id = $this->data['event']['previous_message']['client_msg_id'];
    $local_channel_id = FrontCore::$channels->get_channel($this->slack_channel_id, 'slack_id')->id;
    $local_message_id = FrontCore::$channels->get_message($slack_message_id, 'slack_id')->id;

    Db::i()->delete_row('channel_messages', 'id', $local_message_id);

    $clients_message = [
      'action' => 'message_delete',
      'channel_id' => $local_channel_id,
      'id' => $local_message_id
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
