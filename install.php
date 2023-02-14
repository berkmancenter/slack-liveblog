<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function slack_liveblog_install() {
  $db_version = 1.0;
  global $wpdb;

  if (slack_liveblog_is_version_lower(1.1)) {
    $sql = "CREATE TABLE IF NOT EXISTS slack_liveblog_channels (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      name varchar(777) NOT NULL,
      slack_id varchar(20) NOT NULL,
      owner_id varchar(20) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS slack_liveblog_channel_messages (
      channel_id MEDIUMINT NOT NULL,
      message TEXT DEFAULT '',
      author_id MEDIUMINT NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    dbDelta($sql);

    $sql = "CREATE TABLE IF NOT EXISTS slack_liveblog_authors (
      id MEDIUMINT NOT NULL AUTO_INCREMENT,
      slack_id varchar(20) NOT NULL,
      name varchar(777) NOT NULL,
      image varchar(777),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    );";
    dbDelta($sql);
  }

  update_option('slack_liveblog_version', $db_version);
}

function slack_liveblog_is_version_lower($version) {
  return !get_option('slack_liveblog_version') || floatval(get_option('slack_liveblog_version')) < $version;
}
