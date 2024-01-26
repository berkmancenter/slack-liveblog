<?php

namespace SlackLiveblog\EventConsumers;

use SlackLiveblog\FrontCore;
use SlackLiveblog\Db;

class Reaction extends Consumer {
  public function consume() {
    $rounded_timestamp_microsecs_to_milisecs = "SQL_FUNC:CONCAT(DATE_FORMAT(FROM_UNIXTIME(" . $this->data['event']['item']['ts'] . "), '%Y-%m-%d %H:%i:%s'), '.', LPAD(ROUND(MICROSECOND(FROM_UNIXTIME(" . $this->data['event']['item']['ts'] . ")) / 1000), 3, '0'))";
    $local_message = FrontCore::$channels->get_message($rounded_timestamp_microsecs_to_milisecs, 'remote_created_at');
    $short_code = $this->data['event']['reaction'];
    $type = $this->data['event']['type'];

    $emoji = Db::i()->get_row('emojis', ['*'], [
      'short_code' => $short_code,
    ]);

    if (!$emoji || !$local_message) {
      return false;
    }

    $reaction = Db::i()->get_row('messages_reactions', ['*'], [
      'emoji_id' => $emoji->id,
      'message_id' => $local_message->id,
    ]);

    if (!$reaction) {
      Db::i()->insert_row('messages_reactions', [
        'emoji_id' => $emoji->id,
        'message_id' => $local_message->id,
        'counted' => 1
      ]);
    } else {
      if ($type === 'reaction_added') {
        $reaction->counted++;
      } else {
        $reaction->counted--;
      }

      Db::i()->update_row(
        'messages_reactions',
        [
          'counted' => $reaction->counted,
        ],
        [
          'emoji_id' => $emoji->id,
          'message_id' => $local_message->id,
        ],
      );
    }

    FrontCore::$channels->update_local_message([
      'updated_at' => date('Y-m-d H:i:s'),
    ], [
      'id' => $local_message->id,
    ]);
  }
}
