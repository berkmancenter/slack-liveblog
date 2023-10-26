<?php

namespace SlackLiveblog;

use JoliCode\Slack\ClientFactory;

class AdminActions {
  public function slack_liveblog_admin_init() {
    switch (@$_REQUEST['action']) {
      case 'save-access-token':
        $this->save_access_token();
        break;
    }

    switch (@$_REQUEST['page']) {
      case 'slack_liveblog_settings':
        $this->settings_view();
        break;
      case 'slack_liveblog_channels':
        $this->channels_view();
        break;
    }
  }

  public function slack_liveblog_ajax_actions() {
    $response = [];
    $sub_action = $_POST['sub_action'];

    switch ($sub_action) {
      case 'channel-new':
        $response = $this->create_new_channel();
        break;
      case 'channel-toggle':
        $response = $this->toggle_channel();
        break;
      case 'update-messages-sorting':
        $response = $this->update_message_sorting();
        break;
      case 'update-refresh-interval':
        $response = $this->update_refresh_interval();
        break;
      case 'update-delay':
        $response = $this->update_delay();
        break;
      case 'connect-workspace':
        $response = $this->connect_workspace();
        break;
      case 'channels-list':
        $response = $this->channels_list();
        break;
    }

    $this->send_json_response($response);
  }

  private function send_json_response($response) {
    header('Content-Type: application/json');

    if (isset($response['error'])) {
      http_response_code(400);
    }

    echo json_encode($response);
    die();
  }

  private function channels_view() {
    $settings_url = admin_url('admin.php?page=slack_liveblog_settings');

    $channels_list = Templates::load_template('channels_list', [
      'channels' => AdminCore::$channels->get_channels(),
    ], true);

    Templates::load_template('channels', [
      'channels_list' => $channels_list,
      'settings_url' => $settings_url,
      'workspaces' => AdminCore::$workspaces->get_workspaces()
    ]);
  }

  private function settings_view() {
    $workspaces = AdminCore::$workspaces->get_workspaces();

    Templates::load_template('settings', [
      'workspaces' => $workspaces
    ]);
  }

  private function toggle_channel() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $channel = Db::i()->get_row('channels', ['closed'], ['id' => $id]);

    if (!$channel) {
      return false;
    }

    $new_status = $channel->closed === '1' ? '0' : '1';

    $update_result = Db::i()->update_row('channels', ['closed' => $new_status], ['id' => $id]);

    return $update_result;
  }

  private function update_message_sorting() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['messages_sorting']) === false || empty($_POST['messages_sorting'])) {
      $errors[] = 'Messages sorting must be selected.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $messages_sorting = $_POST['messages_sorting'];

    $update_result = Db::i()->update_row('channels', ['sorting' => $messages_sorting], ['id' => $id]);

    return $update_result;
  }

  private function update_refresh_interval() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['refresh_interval']) === false || empty($_POST['refresh_interval'])) {
      $errors[] = 'Refresh interval must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $refresh_interval = $_POST['refresh_interval'];

    $update_result = Db::i()->update_row('channels', ['refresh_interval' => $refresh_interval], ['id' => $id]);

    return $update_result;
  }

  private function update_delay() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['delay']) === false) {
      $errors[] = 'Delay must be provided';
    }

    if (isset($_POST['delay']) === true && (is_numeric($_POST['delay']) === false || (intval($_POST['delay']) != $_POST['delay']) || intval($_POST['delay']) < 0)) {
      $errors[] = 'Delay must be an integer number greater than or equal to 0.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $delay = $_POST['delay'];

    $update_result = Db::i()->update_row('channels', ['delay' => $delay], ['id' => $id]);

    return $update_result;
  }

  private function connect_workspace() {
    $errors = [];

    if (isset($_POST['access_token']) === false || empty($_POST['access_token'])) {
      $errors[] = 'Access token must be provided.';
    }

    if (isset($_POST['workspace_name']) === false || empty($_POST['workspace_name'])) {
      $errors[] = 'Workspace name must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    try {
      $new_workspace_data = [
        'name' => $_POST['workspace_name']
      ];

      Db::i()->insert_row('workspaces', $new_workspace_data);

      $new_workspace_id = Db::i()->get_last_inserted_id();
      $redirect_callback_uri = admin_url("admin.php?page=slack_liveblog_settings&action=save-access-token&workspace_id={$new_workspace_id}");
      $redirect_callback_uri_encoded = urlencode(admin_url("admin.php?page=slack_liveblog_settings&action=save-access-token&workspace_id={$new_workspace_id}"));
      $app_manifest = file_get_contents(SLACK_LIVEBLOG_DIR_PATH . 'slack_app_manifest.json');
      $app_manifest = str_replace(
        ['###SITE_URL###', '###REDIRECT_URL###'],
        [site_url(), $redirect_callback_uri],
        $app_manifest
      );

      $body = [
        'token' => $_POST['access_token'],
        'manifest' => $app_manifest
      ];
  
      $args = [
        'body' => $body
      ];

      $response = wp_remote_post('https://slack.com/api/apps.manifest.create', $args);
      $response_body = json_decode($response['body']);

      if ($response_body->ok === false) {
        return [
          'error' => 'Something went wrong, check if your access token is correct.'
        ];
      }

      $data = [
        'redirect_url' => $redirect_callback_uri,
        'client_id' => $response_body->credentials->client_id,
        'client_secret' => $response_body->credentials->client_secret,
        'verification_token' => $response_body->credentials->verification_token,
        'signing_secret' => $response_body->credentials->signing_secret
      ];

      Db::i()->update_row('workspaces', $data, ['id' => $new_workspace_id]);

      return [
        'success' => 'New workspace has been connected.',
        'redirect_url' => "https://slack.com/oauth/v2/authorize?scope=groups:history,groups:write,incoming-webhook,reactions:read,users:read,conversations.connect:write&redirect_uri={$redirect_callback_uri_encoded}&client_id={$response_body->credentials->client_id}"
      ];
    } catch(\Exception $e) {
      error_log($e);

      return [
        'error' => 'Something went wrong, check if your access token is correct.'
      ];
    }
  }

  private function save_access_token() {
    $workspace_id = $_GET['workspace_id'] ?? null;
    $authorization_code = $_GET['code'] ?? null;

    if (!$workspace_id || !$authorization_code) {
      return false;
    }

    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $_GET['workspace_id']]);

    if (!$workspace) {
      return false;
    }

    $body = [
      'client_id' => $workspace->client_id,
      'client_secret' => $workspace->client_secret,
      'redirect_uri' => $workspace->redirect_url,
      'code' => $_GET['code'],
    ];

    $args = [
      'body' => $body
    ];

    $response = wp_remote_post('https://slack.com/api/oauth.v2.access', $args);

    if (is_wp_error($response)) {
      return false;
    }

    $response_body = json_decode($response['body']);

    if ($response_body->ok === true) {
      Db::i()->update_row('workspaces', ['access_token' => $response_body->access_token], ['id' => $workspace->id]);
      $this->get_and_save_workspace_team_id($workspace->id);
    }

    return true;
  }

  private function get_and_save_workspace_team_id($workspace_id) {
    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $workspace_id]);

    $body = [
      'token' => $workspace->access_token
    ];

    $args = [
      'body' => $body
    ];

    $response = wp_remote_post('https://slack.com/api/auth.teams.list', $args);

    if (is_wp_error($response)) {
      return false;
    }

    $response_body = json_decode($response['body']);

    if ($response_body->ok !== true) {
      return false;
    }

    Db::i()->update_row('workspaces', ['team_id' => $response_body->teams[0]->id], ['id' => $workspace->id]);

    return true;
  }

  private function create_new_channel() {
    $errors = [];

    if (isset($_POST['name']) === false || empty($_POST['name'])) {
      $errors[] = 'Channel name must be provided.';
    }

    if (isset($_POST['user-id']) === false || empty($_POST['user-id'])) {
      $errors[] = 'Slack member ID must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    try {
      $channel_name = strtolower($_POST['name']);
      $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $_POST['workspace']]);
      $client = ClientFactory::create($workspace->access_token);
      $new_channel = AdminCore::$channels->create_slack_channel($client, $channel_name, $workspace);
  
      $invite_result = AdminCore::$channels->invite_user_to_channel($client, $new_channel->getId(), $_POST['user-id']);
      if (!$invite_result) {
        throw new Exception('Failed to invite user to channel');
      }
  
      $new_channel_data = [
        'name' => $channel_name,
        'uuid' => Helpers::get_uuid(),
        'slack_id' => $new_channel->getId(),
        'owner_id' => $_POST['user-id'],
        'workspace_id' => $workspace->id,
        'refresh_interval' => $_POST['refresh-interval']
      ];
  
      Db::i()->insert_row('channels', $new_channel_data);
    } catch(\Exception $e) {
      error_log($e);

      return [
        'error' => 'Something went wrong.<br>Please double check your slack member ID and/or that the channel name you entered does not conflict with an existing channel name.'
      ];
    }
  }

  private function channels_list() {
    $channels_list = Templates::load_template('channels_list', [
      'channels' => AdminCore::$channels->get_channels(),
    ], true);

    return $channels_list;
  }
}
