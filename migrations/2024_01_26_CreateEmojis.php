<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class CreateEmojis extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}slack_liveblog_emojis (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      short_code TINYTEXT NOT NULL,
      unicode TINYTEXT NOT NULL,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);

    $emojis_db = [];
    $emojis_json = WP_PLUGIN_DIR . '/liveblog-with-slack/resources/emojis.json';
    $emojis = json_decode(file_get_contents($emojis_json));

    foreach (get_object_vars($emojis) as $short_code => $unicode) {
      $emojis_db[] = "('{$short_code}', '{$unicode}')";
    }

    $values = implode(',', $emojis_db);
    $query = "INSERT INTO {$wpdb->prefix}slack_liveblog_emojis (short_code, unicode) VALUES {$values}";

    $wpdb->query($query);
  }

  public function rollback() {
    global $wpdb;

    $wpdb->query('DROP TABLE ' . $wpdb->prefix . 'slack_liveblog_emojis');
  }
}
