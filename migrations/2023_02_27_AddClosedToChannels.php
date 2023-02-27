<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class AddClosedToChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      ADD COLUMN
        closed boolean NOT NULL DEFAULT false;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      DROP COLUMN
        closed"
    );
  }
}
