<?php

namespace SlackLiveblog;

class FrontCore {
  public static $events = null;
  public static $channels = null;
  public static $live = null;
  public static $db = null;
  public static $helpers = null;

  public static function init() {
    // init modules
    self::$events = new Events();
    self::$channels = new Channels();
    self::$live = new Live();
    self::$db = new Db();
    self::$helpers = new Helpers();

    add_action('wp_enqueue_scripts', array(self::class, 'add_assets'));
  }

  public static function add_assets() {
    wp_enqueue_script('slack_liveblog_front_jquery', plugins_url('node_modules/jquery/dist/jquery.min.js', dirname(__FILE__)), array());
    wp_enqueue_script('slack_liveblog_front_liveblog', plugins_url('front/dist/index.js', dirname(__FILE__)), array());
    wp_enqueue_style('slack_liveblog_front_liveblog', plugins_url('front/dist/index.css', dirname(__FILE__)), array());
  }
}
