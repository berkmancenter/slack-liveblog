<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddDeletedAndUpdatedAtToChannelMessages extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        updated_at TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3);
    ";
    $wpdb->query($sql);

    $sql = "
      UPDATE
        {$wpdb->prefix}slack_liveblog_channel_messages
      SET
        updated_at = created_at;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        deleted BOOLEAN NOT NULL DEFAULT false;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        updated_at;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        deleted;
    ";
    $wpdb->query($sql);
  }
}
