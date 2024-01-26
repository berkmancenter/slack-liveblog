<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class CreateMessagesReactions extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_messages_reactions (
      emoji_id MEDIUMINT NOT NULL,
      message_id MEDIUMINT NOT NULL,
      counted MEDIUMINT NOT NULL
    );";
    dbDelta($sql);
  }

  public function rollback() {
    global $wpdb;

    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_messages_reactions');
  }
}
