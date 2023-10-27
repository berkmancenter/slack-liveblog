<?php

namespace SlackLiveblog;

/**
 * Class FrontCore
 *
 * Initializes front-end modules and assets.
 *
 * @package SlackLiveblog
 */
class FrontCore {
  /** @var Events|null Events module instance. */
  public static $events = null;
  /** @var Channels|null Channels module instance. */
  public static $channels = null;
  /** @var Live|null Live module instance. */
  public static $live = null;
  /** @var FrontActions|null FrontActions module instance. */
  public static $front_actions = null;

  /**
   * Initializes FrontCore components and actions.
   *
   * @return void
   */
  public static function init() {
    self::init_modules();
    self::init_actions();
  }

  /**
   * Initialize the various modules used on the front-end.
   *
   * @return void
   */
  private static function init_modules() {
    self::$events = new Events();
    self::$channels = new Channels();
    self::$live = new Live();
    self::$front_actions = new FrontActions();
  }

  /**
   * Initializes the WordPress actions for the front-end.
   *
   * @return void
   */
  private static function init_actions() {
    add_action('wp_enqueue_scripts', [self::class, 'add_assets']);
  }

  /**
   * Adds necessary assets (CSS and JS) for the frontend.
   *
   * @return void
   */
  public static function add_assets() {
    // Enqueue frontend main scripts and styles
    wp_enqueue_script('slack_liveblog_front_liveblog_index', plugins_url('dist/front/index.js', dirname(__FILE__)), []);
    wp_enqueue_style('slack_liveblog_front_liveblog', plugins_url('dist/front/index.css', dirname(__FILE__)), []);
    
    // Enqueue embed scripts
    $embed_scripts = [
      'mastodon' => 'resources/js/mastodon_embed.js',
      'twitter'  => 'resources/js/twitter_embed.js',
      'youtube'  => 'resources/js/youtube_embed.js',
    ];

    foreach ($embed_scripts as $key => $path) {
      wp_enqueue_script("slack_liveblog_front_liveblog_{$key}_embed", plugins_url($path, dirname(__FILE__)), []);
    }
  }
}
