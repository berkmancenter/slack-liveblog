<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddRemoteCreatedAtToChannelMessages extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        remote_created_at TIMESTAMP(3) NOT NULL;
    ";
    $wpdb->query($sql);

    $sql = "
      UPDATE
        {$wpdb->prefix}slack_liveblog_channel_messages
      SET
        remote_created_at = created_at;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        remote_created_at;
    ";
    $wpdb->query($sql);
  }
}
