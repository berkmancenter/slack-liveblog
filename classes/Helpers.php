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
}
