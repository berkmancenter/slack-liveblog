<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class CreateTables extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_channels (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      name VARCHAR(777) NOT NULL,
      slack_id VARCHAR(20) NOT NULL,
      owner_id VARCHAR(20) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_channel_messages (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      channel_id MEDIUMINT NOT NULL,
      message TEXT DEFAULT '',
      author_id MEDIUMINT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_authors (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      slack_id VARCHAR(20) NOT NULL,
      name VARCHAR(777) NOT NULL,
      image VARCHAR(777),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_channels');
    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_channel_messages');
    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_authors');
  }
}
