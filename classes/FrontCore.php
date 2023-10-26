<?php

namespace SlackLiveblog;

class FrontCore {
  public static $events = null;
  public static $channels = null;
  public static $live = null;
  public static $db = null;
  public static $front_actions = null;

  public static function init() {
    // init modules
    self::$events = new Events();
    self::$channels = new Channels();
    self::$live = new Live();
    self::$front_actions = new FrontActions();

    add_action('wp_enqueue_scripts', array(self::class, 'add_assets'));
  }

  public static function add_assets() {
    wp_enqueue_script('slack_liveblog_front_liveblog_index', plugins_url('dist/front/index.js', dirname(__FILE__)), array());
    wp_enqueue_style('slack_liveblog_front_liveblog', plugins_url('dist/front/index.css', dirname(__FILE__)), array());
    wp_enqueue_script('slack_liveblog_front_liveblog_mastodon_embed', plugins_url('resources/js/mastodon_embed.js', dirname(__FILE__)), array());
    wp_enqueue_script('slack_liveblog_front_liveblog_twitter_embed', plugins_url('resources/js/twitter_embed.js', dirname(__FILE__)), array());
    wp_enqueue_script('slack_liveblog_front_liveblog_youtube_embed', plugins_url('resources/js/youtube_embed.js', dirname(__FILE__)), array());
  }
}
