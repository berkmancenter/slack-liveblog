<?php

namespace SlackLiveblog;

use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;

/**
 * Class Channels
 *
 * List of channels
 *
 * @package SlackLiveblog
 */
class Channels {
  public function slack_liveblog_channels_init() {
    global $wpdb;
    $this->database = $wpdb;

    if (@$_POST['action'] === 'new-channel') {
      try {
        $this->create_new_channel();
        $message = 'New channel has been created.';
      } catch (\Throwable $th) {
        error_log(print_r($th, true));
        $message = 'There has been a problem with creating a new channel.';
      }
    }

    Templates::load_template('channels', [
      'message' => @$message,
      'channels' => $this->get_channels()
    ]);
  }

  private function create_new_channel() {
    $client = ClientFactory::create(AdminCore::$settings->plugin_settings['settings_form_field_api_auth_token']);
    $new_channel = $client->conversationsCreate(
      [
        'is_private' => true,
        'name' => strtolower($_POST['name'])
      ]
    )->getChannel();
    $new_channel_id = $new_channel->getId();

    $client->conversationsInvite([
      'channel' => $new_channel_id,
      'users' => $_POST['user-id']
    ]);

    $this->create_local_channel([
      'name' => $_POST['name'],
      'slack_id' => $new_channel_id,
      'user_id' => $_POST['user-id']
    ]);
  }

  private function get_channels() {
    $query = '
      SELECT
        *
      FROM
        slack_liveblog_channels
    ';

    return $this->database->get_results($query);
  }

  private function create_local_channel($data) {
    $query = "
      INSERT INTO slack_liveblog_channels
        (name, slack_id, owner_id)
      VALUES
        (%s, %s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$data['name'], $data['slack_id'], $data['user_id']]
    );

    return $this->database->query($query);
  }
}
