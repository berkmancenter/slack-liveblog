<?php

namespace SlackLiveblog;

class FrontCore {
  public static $settings = null;
  public static $events = null;
  public static $channels = null;

  public static function init() {
    // init modules
    self::$settings = get_option('slack_liveblog_settings');
    self::$events = new Events();
    self::$channels = new Channels();
  }
}
