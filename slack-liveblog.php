<?php

/*
Plugin Name: Slack liveblog
Description: Slack liveblog plugin makes it possible to link a post/page in Wordpress with a Slack channel.
Author: Harvard Law School, Berkman Klein Center
Author URI: http://law.harvard.edu
Version: 1.0
*/

require 'vendor/autoload.php';
require(__DIR__ . '/install.php');

define('SLACK_LIVEBLOG_DIR_PATH', plugin_dir_path(__FILE__));

// Load env variables
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__.'/.env');

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

// Setup db migrations
if (defined('WP_CLI') && WP_CLI) {
  add_filter('dbi_wp_migrations_path', function ($path) {
    return __DIR__ . '/migrations';
  });
  \DeliciousBrains\WPMigrations\Database\Migrator::instance('migrations');
}
