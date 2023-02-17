<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddSlackIdToMessages extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD COLUMN
        slack_id varchar(50) NOT NULL;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP COLUMN
        slack_id"
    );
  }
}
