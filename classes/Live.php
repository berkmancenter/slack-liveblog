<?php

namespace SlackLiveblog;

/**
 * Class Live
 *
 * Initializes the liveblog application on the front-end.
 *
 * @package SlackLiveblog
 */
class Live {
  public function __construct() {
    add_action('init', [$this, 'slack_liveblog_shortcodes_init']);
  }

  /**
   * Registers the [slack_liveblog] shortcode.
   */
  public function slack_liveblog_shortcodes_init() {
    add_shortcode('slack_liveblog', [$this, 'slack_liveblog_shortcode']);
  }

  /**
   * Renders the liveblog based on the provided attributes.
   *
   * @param array $atts Shortcode attributes.
   * @param null|string $content Shortcode content. Not used.
   * @param string $tag Shortcode tag. Not used.
   * @return string Rendered liveblog or empty string if channel not found.
   */
  public function slack_liveblog_shortcode($atts = [], $content = null, $tag = '' ) {
    if (!isset($atts['channel_id'])) {
      return '';
    }

    $channel = FrontCore::$channels->get_channel(['slack_id' => $atts['channel_id']]);

    if (!$channel) {
      return '';
    }

    list($use_websockets, $ws_url) = $this->configure_websockets($channel);

    $messages_url = get_site_url() . "?action=slack_liveblog_get_channel_messages&channel_id={$channel->uuid}";

    $liveblog = Templates::load_template('liveblog', [
      'ws_url' => $ws_url,
      'messages_url' => $messages_url,
      'channel' => $channel,
      'use_websockets' => $use_websockets
    ], true);

    return $liveblog;
  }

  /**
   * Configures websockets for real-time updates.
   *
   * @param object $channel Channel object.
   * @return array Configuration array with websocket usage status and URL.
   */
  private function configure_websockets($channel) {
    $use_websockets = 'false';
    $ws_url = null;

    if (@$_ENV['SLACK_LIVEBLOG_USE_WEBSOCKETS'] === 'true') {
      $use_websockets = 'true';
      $ws_url = $_ENV['SLACK_LIVEBLOG_WS_SERVER_CLIENT_URL'] . "?channel_id={$channel->uuid}";
    }

    return [$use_websockets, $ws_url];
  }
}
