<?php

function slack_liveblog_upgrade($upgrader_object, $options) {
  if($options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
    foreach($options['plugins'] as $plugin) {
      if($plugin === 'slack-liveblog') {
        // Run db migrations
        $migrator = \DeliciousBrains\WPMigrations\Database\Migrator::instance();
        $migrator->run();

        slack_liveblog_upgrade_tasks();

        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_version = $plugin_data['Version'];
        update_option('slack_liveblog_version', $plugin_version);
      }
    }
  }
}

function slack_liveblog_upgrade_tasks() {}
