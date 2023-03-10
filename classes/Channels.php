<?php

namespace SlackLiveblog;

use JoliCode\Slack\Api\Client;
use JoliCode\Slack\ClientFactory;

/**
 * Class Channels
 *
 * Channels related stuff
 *
 * @package SlackLiveblog
 */
class Channels {
  private $database;
  private $slack_client;

  public function __construct() {
    global $wpdb;
    $this->database = $wpdb;
    // To get timestamp values in UTC
    $this->database->query('SET time_zone = \'+00:00\';');
    $this->slack_client = ClientFactory::create(PluginSettings::i()->get('settings_form_field_api_auth_token'));
    $this->init_front_actions();
  }

  private function init_front_actions() {
    $this->front_get_channel_messages();
  }

  private function front_get_channel_messages() {
    if (($_GET['action'] ?? '') !== 'slack_liveblog_get_channel_messages' || !isset($_GET['channel_id'])) {
      return;
    }

    $channel_uuid = filter_input(INPUT_GET, 'channel_id', FILTER_SANITIZE_STRING);
    $channel = $this->get_channel($channel_uuid, 'uuid');

    if (!$channel) {
      echo json_encode(['Channel not found']);
      die();
    }

    $channel_messages = $this->get_channel_messages($channel->id);
    $channel_messages = array_map(function ($message) {
      return [
        'id' => $message->id,
        'body' => $message->message,
        'author' => $message->name,
        'created_at' => $message->created_at
      ];
    }, $channel_messages);

    echo json_encode($channel_messages);
    die();
  }

  public function slack_liveblog_channels_init() {
    $current_url = admin_url("admin.php?page={$_GET['page']}");

    switch (@$_REQUEST['action']) {
      case 'channel-new':
        try {
          $this->create_new_channel();
          $message = 'New channel has been created.';
        } catch (\Throwable $th) {
          $message = 'There has been a problem with creating a new channel.';
        }
        break;
      case 'channel-open':
        $this->open_channel($_GET['id']);
        break;
      case 'channel-close':
        $this->close_channel($_GET['id']);
        break;
    }

    Templates::load_template('channels', [
      'message' => @$message ?? @$_GET['message'],
      'channels' => $this->get_channels(),
      'slack_home_path' => $_ENV['SLACK_LIVEBLOG_CHECKBOX_FIELD_TEAM_HOME'] ?? '',
      'current_url' => $current_url
    ]);
  }

  public function get_open_channels_slack_ids() {
    $rows = Db::i()->get_rows('channels', ['slack_id'], ['closed' => '0']);

    return array_map(function ($c) { return $c->{'slack_id'}; }, $rows);
  }

  public function create_new_channel() {
    if (empty($_POST['name'])) {
      throw new Exception('Channel name is required');
    }

    if (empty($_POST['user-id'])) {
      throw new Exception('User ID is required');
    }

    $channel_name = strtolower($_POST['name']);

    $new_channel = $this->create_slack_channel($channel_name);

    $invite_result = $this->invite_user_to_channel($new_channel->getId(), $_POST['user-id']);
    if (!$invite_result) {
      throw new Exception('Failed to invite user to channel');
    }

    $this->create_local_channel([
      'name' => $channel_name,
      'slack_id' => $new_channel->getId(),
      'user_id' => $_POST['user-id']
    ]);
  }

  public function create_slack_channel($name) {
    $new_channel = $this->slack_client->conversationsCreate([
      'is_private' => true,
      'name' => $name
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
    $query = "
      SELECT
        *
      FROM
        {$this->database->prefix}slack_liveblog_channels
    ";

    return $this->database->get_results($query);
  }

  public function get_channel($value, $field = 'id') {
    return Db::i()->get_row('channels', ['*'], [$field => $value]);
  }

  public function create_local_channel($data) {
    $query = "
      INSERT INTO {$this->database->prefix}slack_liveblog_channels
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
      INSERT INTO {$this->database->prefix}slack_liveblog_channel_messages
        (channel_id, message, author_id, slack_id)
      VALUES
        (%s, %s, %s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$data['channel_id'], $data['message'], $data['author_id'], $data['slack_id']]
    );

    $this->database->query($query);

    return $this->get_message($this->database->insert_id);
  }

  public function get_message($value, $field = 'id') {
    $row =  Db::i()->get_row('channel_messages', ['*'], [$field => $value]);

    return $row;
  }

  public function get_author($value, $field = 'id') {
    $row =  Db::i()->get_row('authors', ['*'], [$field => $value]);

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
      INSERT INTO {$this->database->prefix}slack_liveblog_authors
        (slack_id, name, image)
      VALUES
        (%s, %s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$slack_id, $user->getRealName(), '']
    );

    $this->database->query($query);

    $new_author_id = $this->database->insert_id;

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
        cm.created_at AS created_at,
        cm.id AS id
      FROM
        {$this->database->prefix}slack_liveblog_channel_messages cm
      LEFT JOIN
        {$this->database->prefix}slack_liveblog_authors a
        ON
        cm.author_id = a.id
      WHERE
        channel_id = $channel_id
      ORDER BY
        cm.created_at DESC
    ";

    return $this->database->get_results($query);
  }

  public function update_local_message($data, $where) {
    return Db::i()->update_row('channel_messages', $data, $where);
  }

  private function open_channel($id) {
    return Db::i()->update_row('channels', ['closed' => false], ['id' => $id]);
  }

  private function close_channel($id) {
    return Db::i()->update_row('channels', ['closed' => true], ['id' => $id]);
  }
}
