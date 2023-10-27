<?php

namespace SlackLiveblog;

/**
 * Class AdminCore
 *
 * Handles administrative functionalities for the plugin.
 *
 * @package SlackLiveblog
 */
class AdminCore {
  /** @var Menu|null Menu module instance. */
  public static $menu = null;
  /** @var Channels|null Channels module instance. */
  public static $channels = null;
  /** @var Workspaces|null Workspacess module instance. */
  public static $workspaces = null;
  /** @var AdminActions|null AdminActions module instance. */
  public static $actions = null;

  /**
   * Initializes AdminCore components and actions.
   *
   * @return void
   */
  public static function init() {
    self::init_modules();
    self::init_actions();
  }

  /**
   * Initializes various modules used in the admin.
   *
   * @return void
   */
  private static function init_modules() {
    self::$menu = new Menu();
    self::$channels = new Channels();
    self::$workspaces = new Workspaces();
    self::$actions = new AdminActions();
  }

  /**
   * Initializes WordPress actions for the admin.
   *
   * @return void
   */
  private static function init_actions() {
    add_action('admin_enqueue_scripts', [self::class, 'add_assets']);
    add_action('wp_ajax_slack_liveblog_admin', [self::$actions, 'slack_liveblog_ajax_actions']);
  }

  /**
   * Adds necessary assets (CSS and JS) for the admin.
   *
   * @return void
   */
  public static function add_assets() {
    wp_enqueue_script('slack_liveblog_admin_vendor', plugins_url('dist/admin/vendor.js', dirname(__FILE__)), []);
    wp_enqueue_style('slack_liveblog_admin_vendor', plugins_url('dist/admin/vendor.css', dirname(__FILE__)), []);
    wp_enqueue_style('slack_liveblog_admin', plugins_url('resources/css/admin.css', dirname(__FILE__)), []);
    wp_enqueue_script('slack_liveblog_admin', plugins_url('resources/js/admin.js', dirname(__FILE__)), []);
  }
}
