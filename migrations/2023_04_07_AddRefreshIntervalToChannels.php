<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;
use \SlackLiveblog\Db;

class AddRefreshIntervalToChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      ADD COLUMN
        refresh_interval SMALLINT NOT NULL;
    ";
    $wpdb->query($sql);

    $exisiting_channels = Db::i()->get_rows('channels');
    foreach ($exisiting_channels as $channel) {
      Db::i()->update_row('channels', ['refresh_interval' => 1], ['id' => $channel->id]);
    }
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      DROP COLUMN
        refresh_interval;
    ");
  }
}
