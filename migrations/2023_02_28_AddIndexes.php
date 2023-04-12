<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;
use \SlackLiveblog\Db;
use \SlackLiveblog\Helpers;

class AddIndexes extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      ADD INDEX
        slack_id_index (slack_id);

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD INDEX
        channel_id_index (channel_id);

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      ADD UNIQUE INDEX
        slack_id_index (slack_id);

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_authors
      ADD UNIQUE INDEX
        slack_id_index (slack_id);
    ";

    mysqli_multi_query($wpdb->dbh, $sql);
  }

  public function rollback() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      DROP INDEX
        slack_id_index;

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP INDEX
        channel_id_index;

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channel_messages
      DROP INDEX
        slack_id_index;

      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_authors
      DROP INDEX
        slack_id_index;
    ";

    mysqli_multi_query($wpdb->dbh, $sql);
  }
}
