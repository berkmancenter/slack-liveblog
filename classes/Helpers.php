<?php

namespace SlackLiveblog;

class Helpers {
  private static $instance = null;

  public static function i() {
    if (self::$instance === null) {
      self::$instance = new Helpers();
    }

    return self::$instance;
  }

  public function get_parsed_timezoned_date($date_string) {
    $date = new \DateTime($date_string);
    $timezone = new \DateTimeZone($_ENV['SLACK_LIVEBLOG_TIMEZONE']);
    $date->setTimezone($timezone);
    $formatted_date = $date->format('h:i A');

    return $formatted_date;
  }

  public function get_bool_yes_no($boolean) {
    return $boolean === '1' ? 'Yes' : 'No';
  }

  public function get_uuid() {
    $randomBytes = random_bytes(16);
    $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40);
    $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));

    return $uuid;
  }
}
