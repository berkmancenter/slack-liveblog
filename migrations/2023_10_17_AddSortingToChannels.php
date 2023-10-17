<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;
use \SlackLiveblog\Db;

class AddSortingToChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      ADD COLUMN
        sorting VARCHAR(20) NOT NULL DEFAULT 'desc';
    ";
    $wpdb->query($sql);

    $exisiting_channels = Db::i()->get_rows('channels');
    foreach ($exisiting_channels as $channel) {
      Db::i()->update_row('channels', ['sorting' => 'desc'], ['id' => $channel->id]);
    }
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      DROP COLUMN
        sorting;
    ");
  }
}
