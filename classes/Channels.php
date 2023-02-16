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
  public function __construct() {
    global $wpdb;
    $this->database = $wpdb;
    $this->slack_client = ClientFactory::create(PluginSettings::i()->get('settings_form_field_api_auth_token'));
  }

  public function slack_liveblog_channels_init() {
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

  public function get_channels_field($field) {
    $query = "
      SELECT
        $field
      FROM
        slack_liveblog_channels
    ";

    $rows = $this->database->get_results($query);

    return array_map(function ($c) use ($field) { return $c->{$field}; }, $rows);
  }

  public function create_new_channel() {
    if (empty($_POST['name'])) {
      throw new Exception('Channel name is required');
    }

    if (empty($_POST['user-id'])) {
      throw new Exception('User ID is required');
    }

    $new_channel = $this->create_slack_channel($_POST['name']);

    $invite_result = $this->invite_user_to_channel($new_channel->getId(), $_POST['user-id']);
    if (!$invite_result) {
      throw new Exception('Failed to invite user to channel');
    }

    $this->create_local_channel([
      'name' => $_POST['name'],
      'slack_id' => $new_channel->getId(),
      'user_id' => $_POST['user-id']
    ]);
  }

  public function create_slack_channel($name) {
    $new_channel = $this->slack_client->conversationsCreate([
      'is_private' => true,
      'name' => strtolower($name)
    ])->getChannel();

    return $new_channel;
  }

  public function invite_user_to_channel($channel_id, $user_id) {
    $invite_result = $this->slack_client->conversationsInvite([
      'channel' => $channel_id,
      'users' => $user_id
    ]);

    return $invite_result->getOk();
  }

  public function get_channels() {
    $query = '
      SELECT
        *
      FROM
        slack_liveblog_channels
    ';

    return $this->database->get_results($query);
  }

  public function get_channel_by_slack_id($slack_id) {
    $query = "
      SELECT
        *
      FROM
        slack_liveblog_channels
      WHERE
        slack_id = '$slack_id'
    ";

    return $this->database->get_row($query);
  }

  public function create_local_channel($data) {
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

  public function create_local_message($data) {
    $query = "
      INSERT INTO slack_liveblog_channel_messages
        (channel_id, message, author_id)
      VALUES
        (%s, %s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$data['channel_id'], $data['message'], $data['author_id']]
    );

    $this->database->query($query);

    return $this->get_local_message($this->database->insert_id);
  }

  public function get_local_message($id) {
    $query = "
      SELECT
        *
      FROM
        slack_liveblog_channel_messages
      WHERE
        id = '$id'
    ";

    return $this->database->get_row($query);
  }

  public function get_author($value, $field = 'id') {
    $query = "
      SELECT
        *
      FROM
        slack_liveblog_authors
      WHERE
        $field = '$value'
    ";

    $row =  $this->database->get_row($query);

    return $row;
  }

  public function create_new_author($slack_id) {
    $client = ClientFactory::create(PluginSettings::i()->get('settings_form_field_api_auth_token'));
    $user = $client->usersInfo(
      [
        'user' => $slack_id
      ]
    )->getUser();

    $query = "
      INSERT INTO slack_liveblog_authors
        (slack_id, name, image)
      VALUES
        (%s, %s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$slack_id, $user->getRealName(), '']
    );

    $new_author_id = $this->database->query($query);

    return $this->get_author($new_author_id);
  }

  public function get_or_create_author_by_slack_id($slack_id) {
    $existing_author = $this->get_author($slack_id, 'slack_id');

    if ($existing_author) {
      return $existing_author;
    }

    return $this->create_new_author($slack_id);
  }

  public function get_channel_messages($channel_id) {
    $query = "
      SELECT
        *,
        cm.created_at as created_at
      FROM
        slack_liveblog_channel_messages cm
      LEFT JOIN
        slack_liveblog_authors a
        ON
        cm.author_id = a.id
      WHERE
        channel_id = $channel_id
      ORDER BY
        cm.created_at DESC
    ";

    return $this->database->get_results($query);
  }
}
