<?php

namespace SlackLiveblog;

class AdminCore {
  public static $menu = null;
  public static $channels = null;
  public static $settings = null;
  public static $db = null;
  public static $actions = null;

  public static function init() {
    // init modules
    self::$menu = new Menu();
    self::$channels = new Channels();
    self::$settings = new Settings();
    self::$db = new Db();
    self::$actions = new AdminActions();

    add_action('admin_enqueue_scripts', array(self::class, 'add_assets'));
    add_action('wp_ajax_slack_liveblog_admin', [self::$actions, 'ajax_actions']);
  }

  public static function add_assets() {
    wp_enqueue_script('slack_liveblog_admin_vendor', plugins_url('dist/vendor.js', dirname(__FILE__)), array());
    wp_enqueue_style('slack_liveblog_admin_vendor', plugins_url('dist/vendor.css', dirname(__FILE__)), array());

    wp_enqueue_style('slack_liveblog_admin', plugins_url('resources/css/admin.css', dirname(__FILE__)), array());
    wp_enqueue_script('slack_liveblog_admin', plugins_url('resources/js/admin.js', dirname(__FILE__)), array());
  }
}
