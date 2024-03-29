<?php

/*
Plugin Name: Liveblog with Slack
Description: Liveblog with Slack is a WordPress plugin designed to bridge the gap between your WordPress website and your Slack workspace. The plugin allows you to establish a seamless connection between a specific WordPress post or page and a corresponding Slack channel, ensuring that every message, update, or edit made in the channel is synced in real-time to the WordPress post.
Author: Harvard Law School, Berkman Klein Center
Author URI: https://cyber.harvard.edu
Version: 1.0
*/

require 'vendor/autoload.php';
require(__DIR__ . '/install.php');
require(__DIR__ . '/upgrade.php');

define('SLACK_LIVEBLOG_DIR_PATH', plugin_dir_path(__FILE__));

// Load env variables if .env file does exist
if (file_exists(__DIR__ . '/.env')) {
  $dotenv = new Symfony\Component\Dotenv\Dotenv();
  $dotenv->load(__DIR__ . '/.env');
}

// Start the core
add_action('plugins_loaded', function () {
  if (is_admin()) {
    \SlackLiveblog\AdminCore::init();
  } else {
    \SlackLiveblog\FrontCore::init();
  }
});

// Trigger the install script on plugin activation
register_activation_hook(__FILE__, 'slack_liveblog_install');
// Trigger the upgrade script
add_action('upgrader_process_complete', 'slack_liveblog_upgrade', 10, 2);

add_filter('dbi_wp_migrations_path', function ($path) {
  return __DIR__ . '/migrations';
});

// Setup db migrations in CLI
if (defined('WP_CLI') && WP_CLI) {
  \DeliciousBrains\WPMigrations\Database\Migrator::instance('migrations');
}
