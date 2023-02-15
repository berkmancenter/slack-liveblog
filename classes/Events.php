<?php

namespace SlackLiveblog;

class Events {
  private $SIGNING_SECRET;

  public function __construct() {
    $this->SIGNING_SECRET = PluginSettings::i()->get('slack_liveblog_checkbox_field_api_signing_secret');

    add_action('init', [$this, 'slack_liveblog_events_init']);
  }

  public function slack_liveblog_events_init() {
    if (strpos($_SERVER[ 'REQUEST_URI' ], '/slack_liveblog_events') !== false && $_SERVER['REQUEST_METHOD'] === 'POST') {
      $this->incoming_data = json_decode(file_get_contents('php://input'), true);

      if ($this->incoming_data['type'] === 'url_verification') {
        echo $this->incoming_data['challenge'];
        die();
      } else {
        $this->handle_event();
      }
    }
  }

  private function handle_event() {
    $channel_id = $this->incoming_data['event']['channel'];
    $instance_channels = FrontCore::$channels->get_channels_field('slack_id');

    if ($this->incoming_data['token'] !== $this->SIGNING_SECRET) {
      $this->respond_event();
    }

    if ($this->incoming_data['event']['type'] !== 'message') {
      $this->respond_event();
    }

    if (in_array($channel_id, $instance_channels) === false) {
      $this->respond_event();
    }

    $local_channel_id = FrontCore::$channels->get_channel_by_slack_id($channel_id)->id;

    $slack_user_id = $this->incoming_data['event']['user'];
    $author = FrontCore::$channels->get_or_create_author_by_slack_id($slack_user_id);

    FrontCore::$channels->create_local_message([
      'channel_id' => $local_channel_id,
      'message' => $this->incoming_data['event']['text'],
      'author_id' => $author->id
    ]);

    $this->respond_event();
  }

  private function respond_event() {
    echo $this->incoming_data['challenge'];
    die();
  }
}
