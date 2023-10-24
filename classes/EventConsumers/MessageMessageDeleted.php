<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class MessageMessageDeleted extends Consumer {
  public function consume(): array {
    $slack_message_id = $this->data['event']['previous_message']['client_msg_id'];
    $local_channel_uuid = FrontCore::$channels->get_channel(['slack_id' => $this->slack_channel_id])->uuid;
    $local_message_id = FrontCore::$channels->get_message($slack_message_id, 'slack_id')->id;

    FrontCore::$channels->update_local_message([
      'deleted' => '1',
      'updated_at' => date('Y-m-d H:i:s')
    ], [
      'id' => $local_message_id
    ]);

    $clients_message = [
      'action' => 'message_deleted',
      'channel_id' => $local_channel_uuid,
      'id' => $local_message_id
    ];

    return [
      'message_body' => $clients_message
    ];
  }
}
