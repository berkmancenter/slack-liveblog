<?php

namespace SlackLiveblog;

/**
 * Class PluginSettings
 *
 * Handle plugin settings
 *
 * @package SlackLiveblog
 */
class PluginSettings {
  private static $instance = null;

  public static function i() {
    if (self::$instance === null) {

      self::$instance = new PluginSettings();
    }

    return self::$instance;
  }

  public function get($key) {
    $key_upper = strtoupper($key);
    if (isset($_ENV[$key_upper])) {
      return $_ENV[$key_upper];
    }

    if (isset($this->all_settings()[$key])) {
      return $this->all_settings()[$key];
    }

    return '';
  }

  public function is_from_env($key) {
    $key_upper = strtoupper($key);
    return isset($_ENV[$key_upper]);
  }

  private function all_settings() {
    return get_option('slack_liveblog_settings');
  }
}
