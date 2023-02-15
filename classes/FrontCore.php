<?php

namespace SlackLiveblog;

class FrontCore {
  public static $settings = null;
  public static $events = null;
  public static $channels = null;
  public static $live = null;

  public static function init() {
    // init modules
    self::$events = new Events();
    self::$channels = new Channels();
    self::$live = new Live();

    add_action('wp_enqueue_scripts', array(self::class, 'add_assets'));
  }

  public static function add_assets() {
    wp_enqueue_style('slack_liveblog_settings', plugins_url('resources/css/liveblog.css', dirname(__FILE__)), array());
  }
}
