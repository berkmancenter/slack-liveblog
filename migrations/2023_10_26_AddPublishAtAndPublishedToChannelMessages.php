<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddPublishAtAndPublishedToChannelMessages extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        published BOOLEAN NOT NULL DEFAULT true;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        publish_at TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3);
    ";
    $wpdb->query($sql);

    $sql = "
      UPDATE
        {$wpdb->prefix}slack_liveblog_channel_messages
      SET
      published = false;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        published;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        publish_at;
    ";
    $wpdb->query($sql);
  }
}
