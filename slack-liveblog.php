<?php

/*
Plugin Name: Slack liveblog
Description: Slack liveblog plugin makes it possible to link a post/page in Wordpress with a Slack channel. It requires an instance of https://github.com/The-Politico/django-slackchat-serializer running to communicate with the Slack API.
Author: Harvard Law School, Berkman Klein Center
Author URI: http://law.harvard.edu
Version: 1.0
*/

require(__DIR__ . '/install.php');

set_include_path(plugin_dir_path(__FILE__) . 'libs/phpseclib');

spl_autoload_register('slack_liveblog_autoloader');

// Start the core plugin
add_action('plugins_loaded', function () {
  if (is_admin()) {
    new \SlackLiveblog\AdminCore;
  } else {
    \SlackLiveblog\FrontCore;
  }
});

// Trigger the install script on plugin activation
register_activation_hook(__FILE__, 'slack_liveblog_install');

/**
 * Callback for the spl_autoload
 *
 * @param $class string
 */
function slack_liveblog_autoloader($class) {
  // include the Composer autoload file
  require 'vendor/autoload.php';

  $parts = explode('\\', $class);

  $parts[0] = 'classes';

  $parts = array_map(function ($item) {
    $item = str_replace('.', '', $item);
    $item = str_replace('_', '-', $item);
    $item = strtolower($item);

    return $item;
  }, $parts);

  $path = join('/', $parts);
  $path = $path . '.php';
  $path = plugin_dir_path(__FILE__) . $path;

  error_log($path);

  if (file_exists($path)) {
    require_once $path;
  }
}
