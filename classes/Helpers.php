<?php

namespace SlackLiveblog;

class Helpers {
  public static function get_bool_yes_no($boolean) {
    return $boolean === '1' ? 'Yes' : 'No';
  }

  public static function get_channel_open_close($status) {
    return $status === '1' ? 'Open' : 'Close';
  }

  public static function get_uuid() {
    $randomBytes = random_bytes(16);
    $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40);
    $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80);
    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($randomBytes), 4));

    return $uuid;
  }

  public static function connected_to_slack_yes_no() {
    $status = PluginSettings::i()->get('settings_form_connected_to_workspace');

    if ($status === 'true') {
      $status_label = 'Connected to workspace';
    } else {
      $status_label = 'Not connected to workspace';
    }

    return $status_label;
  }
}
