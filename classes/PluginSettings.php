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
  private array $cached_settings;

  public static function i() {
    if (self::$instance === null) {

      self::$instance = new PluginSettings();
      self::$instance->cached_database_settings = get_option('slack_liveblog_settings');
    }

    return self::$instance;
  }

  public function get($key) {
    $key_upper = strtoupper($key);
    if (isset($_ENV[$key_upper])) {
      return $_ENV[$key_upper];
    }

    if (isset($this->database_settings()[$key])) {
      return $this->database_settings()[$key];
    }

    return '';
  }

  public function is_from_env($key) {
    $key_upper = strtoupper($key);
    return isset($_ENV[$key_upper]);
  }

  private function database_settings() {
    return $this->cached_database_settings;
  }
}
