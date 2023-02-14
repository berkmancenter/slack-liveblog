<?php

/*
Plugin Name: Slack liveblog
Description: Slack liveblog plugin makes it possible to link a post/page in Wordpress with a Slack channel. It requires an instance of https://github.com/The-Politico/django-slackchat-serializer running to communicate with the Slack API.
Author: Harvard Law School, Berkman Klein Center
Author URI: http://law.harvard.edu
Version: 1.0
*/

require 'vendor/autoload.php';
require(__DIR__ . '/install.php');

// Start the core plugin
add_action('plugins_loaded', function () {
  if (is_admin()) {
    \SlackLiveblog\AdminCore::init();
  } else {
    \SlackLiveblog\FrontCore::init();
  }
});

// Trigger the install script on plugin activation
register_activation_hook(__FILE__, 'slack_liveblog_install');
