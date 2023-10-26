<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class SetDefaultForCreatedAtInChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      MODIFY
        created_at TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3);
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      MODIFY
        created_at TIMESTAMP(3) DEFAULT NULL;
    ");
  }
}
