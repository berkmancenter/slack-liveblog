<?php

namespace SlackLiveblog;

/**
 * Class Helpers
 *
 * Utility functions.
 *
 * @package SlackLiveblog
 */
class Helpers {
  /**
   * Converts boolean status to 'Yes' or 'No'.
   *
   * @param string $boolean Boolean status in string format.
   * @return string 'Yes' if '1', otherwise 'No'.
   */
  public static function get_bool_yes_no($boolean) {
    return $boolean === '1' ? 'Yes' : 'No';
  }

  /**
   * Converts channel status to 'Open' or 'Close'.
   *
   * @param string $status Channel status in string format.
   * @return string 'Open' if '1', otherwise 'Close'.
   */
  public static function get_channel_open_close($status) {
    return $status === '1' ? 'Open' : 'Close';
  }

  /**
   * Generates and return a UUID.
   *
   * @return string Generated UUID.
   */
  public static function get_uuid() {
    $randomBytes = random_bytes(16);
    $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40);
    $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));

    return $uuid;
  }
}
