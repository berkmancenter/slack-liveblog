<?php

namespace SlackLiveblog;

class AdminCore {
  public static $menu = null;
  public static $channels = null;
  public static $settings = null;
  public static $db = null;

  public static function init() {
    // init modules
    self::$menu = new Menu();
    self::$channels = new Channels();
    self::$settings = new Settings();
    self::$db = new Db();

    add_action('admin_enqueue_scripts', array(self::class, 'add_assets'));
  }

  public static function add_assets() {
    wp_enqueue_style('slack_liveblog_admin', plugins_url('resources/css/admin.css', dirname(__FILE__)), array());
  }
}
