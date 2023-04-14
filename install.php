<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function slack_liveblog_install() {
  // Run db migrations
  $migrator = \DeliciousBrains\WPMigrations\Database\Migrator::instance();
  $migrator->setup();
  $migrator->run();

  update_option('slack_liveblog_version', '1.0');
}
