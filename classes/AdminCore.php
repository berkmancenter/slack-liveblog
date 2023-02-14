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
  }
}
