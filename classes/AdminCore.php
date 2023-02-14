<?php

namespace SlackLiveblog;

class AdminCore {
  public static $settings = null;
  public static $menu = null;
  public static $channels = null;

  public static function init() {
    // init modules
    self::$settings = new Settings();
    self::$menu = new Menu();
    self::$channels = new Channels();

    add_action('admin_enqueue_scripts', array(self::class, 'add_assets'));
  }

  public static function add_assets() {
    wp_enqueue_style('slack_liveblog_settings', plugins_url('resources/css/admin.css', dirname(__FILE__)), array());
  }
}
