<?php

namespace SlackLiveblog;

/**
 * Class Settings
 *
 * Plugin settings
 *
 * @package SlackLiveblog
 */
class Settings {
  public $plugin_settings = null;

  public function __construct() {
    $this->init_actions();
    $this->plugin_settings = get_option('slack_liveblog_settings');
  }

  
  private function init_actions() {
    add_action('admin_init', [$this, 'slack_liveblog_settings_init']);
    add_action('admin_enqueue_scripts', array($this, 'add_assets'));
  }

  public function slack_liveblog_settings_init() {
    register_setting('slack_liveblog_settings_page', 'slack_liveblog_settings');

    add_settings_section(
      'slack_liveblog_settings_page_section',
      '',
      '',
      'slack_liveblog_settings_page'
    );

    add_settings_field(
      'slack_liveblog_checkbox_field_api_auth_token',
      'API authentication token',
      [$this, 'slack_liveblog_checkbox_field_api_auth_token_render'],
      'slack_liveblog_settings_page',
      'slack_liveblog_settings_page_section'
    );

    add_settings_field(
      'slack_liveblog_checkbox_field_api_verification_token',
      'API verification token',
      [$this, 'slack_liveblog_checkbox_field_api_verification_token_render'],
      'slack_liveblog_settings_page',
      'slack_liveblog_settings_page_section'
    );

    add_settings_field(
      'slack_liveblog_checkbox_field_team_home',
      'Slack team home URL',
      [$this, 'slack_liveblog_checkbox_field_slack_team_home_render'],
      'slack_liveblog_settings_page',
      'slack_liveblog_settings_page_section'
    );
  }

  public function slack_liveblog_checkbox_field_api_auth_token_render() {
    Templates::load_template('settings_form_field_api_auth_token');
  }

  public function slack_liveblog_checkbox_field_api_verification_token_render() {
    Templates::load_template('settings_form_field_api_verification_token');
  }

  public function slack_liveblog_checkbox_field_slack_team_home_render() {
    Templates::load_template('settings_form_field_slack_team_home');
  }

  public function add_assets() {
    wp_enqueue_style('slack_liveblog_settings', plugins_url('resources/css/admin.css', dirname(__FILE__)), array());
  }

  public function slack_liveblog_options_page() {
    Templates::load_template('settings_form');
  }
}
