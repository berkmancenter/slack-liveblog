<?php

function slack_liveblog_upgrade($upgrader_object, $options) {
  if($options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
    foreach($options['plugins'] as $plugin) {
      if($plugin === 'slack-liveblog') {
        // Run db migrations
        $migrator = \DeliciousBrains\WPMigrations\Database\Migrator::instance();
        $migrator->run();
      }
    }
  }
}
