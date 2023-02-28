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

    $channel = FrontCore::$channels->get_channel($atts['channel_id'], 'slack_id');

    if (!$channel) {
      return '';
    }

    $ws_url = $_ENV['WS_SERVER_CLIENT_URL'] . "?channel_id={$channel->uuid}";
    $messages_url = get_site_url() . "?action=slack_liveblog_get_channel_messages&channel_id={$channel->uuid}";

    $liveblog = Templates::load_template('liveblog', [
      'ws_url' => $ws_url,
      'messages_url' => $messages_url,
      'channel' => $channel
    ], true);

    return $liveblog;
  }
}
