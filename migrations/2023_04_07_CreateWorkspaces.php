<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class CreateWorkspaces extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_workspaces (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      name VARCHAR(777) NOT NULL,
      home_url VARCHAR(150) NOT NULL,
      client_id VARCHAR(50) NOT NULL,
      client_secret VARCHAR(64) NOT NULL,
      verification_token VARCHAR(64) NOT NULL,
      signing_secret VARCHAR(64) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}slack_liveblog_channels
      ADD COLUMN
        workspace_id INT NOT NULL,
      ADD INDEX
        workspace_id_index (workspace_id)
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;

    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_workspaces');
    $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'slack_liveblog_channels DROP INDEX workspace_id_index');
  }
}
