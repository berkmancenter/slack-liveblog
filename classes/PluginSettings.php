<?php

namespace SlackLiveblog;

/**
 * Class PluginSettings
 *
 * Manages plugin settings.
 *
 * @package SlackLiveblog
 */
class PluginSettings {
  /** @var PluginSettings|null Singleton instance */
  private static ?PluginSettings $instance = null;

  /** @var array Cached settings from the database. */
  private array $cached_settings;

  /**
   * Provides singleton instance, creating one if it does not already exist.
   *
   * @return PluginSettings Singleton instance of the class.
   */
  public static function i(): PluginSettings {
    if (self::$instance === null) {
      self::$instance = new PluginSettings();
      self::$instance->cached_database_settings = get_option('slack_liveblog_settings');
    }

    return self::$instance;
  }

  /**
   * Retrieves setting value, checking environment variables first,
   * then database settings. Returns an empty string if setting is not found.
   *
   * @param string $key Key of the setting to retrieve.
   * @return string|mixed Setting value or an empty string if not found.
   */
  public function get(string $key) {
    $key_upper = strtoupper($key);

    if (isset($_ENV[$key_upper])) {
      return $_ENV[$key_upper];
    }

    return $this->cached_settings[$key] ?? '';
  }

  /**
   * Checks if a setting is defined in the environment variables.
   *
   * @param string $key Key of the setting.
   * @return bool True if setting is defined in the environment, false otherwise.
   */
  public function is_from_env(string $key): bool {
    $key_upper = strtoupper($key);
    return isset($_ENV[$key_upper]);
  }
}
