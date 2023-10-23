<?php

namespace SlackLiveblog;

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
  }

  public function get_open_channels_slack_ids() {
    $rows = Db::i()->get_rows('channels', ['slack_id'], ['closed' => '0']);

    return array_map(function ($c) { return $c->{'slack_id'}; }, $rows);
  }

  public function create_slack_channel($client, $name, $workspace) {
    $new_channel = $client->conversationsCreate([
      'is_private' => true,
      'name' => $name
    ])->getChannel();

    return $new_channel;
  }

  public function invite_user_to_channel($client, $channel_id, $user_id) {
    $invite_result = $client->conversationsInvite([
      'channel' => $channel_id,
      'users' => $user_id
    ]);

    return $invite_result->getOk();
  }

  public function get_channels() {
    $query = "
      SELECT
        ch.*,
        wo.name AS workspace_name,
        wo.id AS workspace_id,
        wo.team_id AS workspace_team_id
      FROM
        {$this->database->prefix}slack_liveblog_channels ch
      LEFT JOIN
        {$this->database->prefix}slack_liveblog_workspaces wo
        ON
        ch.workspace_id = wo.id
    ";

    return $this->database->get_results($query);
  }

  public function get_channel($where) {
    return Db::i()->get_row('channels', ['*'], $where);
  }

  public function create_local_message($data) {
    Db::i()->insert_row('channel_messages', $data);

    $message_id = Db::i()->get_last_inserted_id();

    return $this->get_message($message_id);
  }

  public function get_message($value, $field = 'id') {
    $row =  Db::i()->get_row('channel_messages', ['*'], [$field => $value]);

    return $row;
  }

  public function get_author($value, $field = 'id') {
    $row =  Db::i()->get_row('authors', ['*'], [$field => $value]);

    return $row;
  }

  public function create_new_author($slack_id, $workspace_id) {
    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $workspace_id]);

    $client = ClientFactory::create($workspace->access_token);
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

  public function get_or_create_author_by_slack_id($slack_id, $workspace_id) {
    $existing_author = $this->get_author($slack_id, 'slack_id');

    if ($existing_author) {
      return $existing_author;
    }

    return $this->create_new_author($slack_id, $workspace_id);
  }

  public function get_channel_messages($channel_id, $from_time = '') {
    $query_variables = [$channel_id];
    if ($from_time) {
      $query_variables[] = $from_time;
      $from_time = "AND UNIX_TIMESTAMP(cm.created_at) >= %s";
    }

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
        channel_id = %s
        {$from_time}
      ORDER BY
        cm.created_at DESC
    ";

    $query = $this->database->prepare(
      $query,
      $query_variables
    );

    return $this->database->get_results($query);
  }

  public function update_local_message($data, $where) {
    return Db::i()->update_row('channel_messages', $data, $where);
  }
}
