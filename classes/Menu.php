<?php

namespace SlackLiveblog;

class Menu {
  public function __construct() {
    add_action('admin_menu', [$this, 'slack_liveblog_add_admin_menu']);
  }

  public function slack_liveblog_add_admin_menu() {
    add_menu_page('Slack Liveblog', 'Slack Liveblog', 'manage_options', 'slack_liveblog_channels', [AdminCore::$actions, 'slack_liveblog_channels_init'], plugins_url('slack-liveblog/resources/img/slack_logo.svg'));
    add_submenu_page('slack_liveblog_channels', 'Channels', 'Channels', 'manage_options', 'slack_liveblog_channels', [AdminCore::$actions, 'slack_liveblog_channels_init']);
    add_submenu_page('slack_liveblog_channels', 'Settings', 'Settings', 'manage_options', 'slack_liveblog_settings', [AdminCore::$settings, 'slack_liveblog_options_page']);
  }
}
