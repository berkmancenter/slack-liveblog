<?php

namespace SlackLiveblog;

class Live {
  public function __construct() {
    add_action('init', [$this, 'slack_liveblog_shortcodes_init']);
  }

  public function slack_liveblog_shortcodes_init() {
    add_shortcode('slack_liveblog', [$this, 'slack_liveblog_shortcode']);
  }

  public function slack_liveblog_shortcode($atts = [], $content = null, $tag = '' ) {
    if (!isset($atts['channel_id'])) {
      return '';
    }

    $channel = FrontCore::$channels->get_channel_by_slack_id($atts['channel_id']);

    if (!$channel) {
      return '';
    }

    $messages = FrontCore::$channels->get_channel_messages($channel->id);

    $liveblog = Templates::load_template('liveblog', [
      'messages' => $messages,
      'channel' => $channel
    ], true);

    return $liveblog;
  }
}
