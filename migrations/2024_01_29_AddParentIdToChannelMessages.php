<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddParentIdToChannelMessages extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        parent_id MEDIUMINT;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        parent_id;
    ";
    $wpdb->query($sql);
  }
}
