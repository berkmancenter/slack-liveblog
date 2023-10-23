<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class ChangeMessageTimestampPrecision extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      MODIFY COLUMN
        created_at TIMESTAMP(3);
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      MODIFY COLUMN
        created_at TIMESTAMP;
    ";
    $wpdb->query($sql);
  }
}
