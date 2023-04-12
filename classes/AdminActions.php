<?php

namespace SlackLiveblog;

class AdminActions {
  public function slack_liveblog_admin_init() {
    switch (@$_REQUEST['action']) {
      case 'channel-new':
        AdminCore::$channels->create_new_channel();
        break;
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

    switch ($_POST['sub_action']) {
      case 'channel-toggle':
        $response = $this->toggle_channel($_POST['id']);
        break;
      case 'update-refresh-interval':
        $response = $this->update_refresh_interval($_POST['id'], $_POST['refresh_interval']);
        break;
      case 'connect-workspace':
        $response = $this->connect_workspace();
        break;
    }

    header('Content-Type: application/json');

    if (isset($response['error'])) {
      http_response_code(400);
    }

    echo json_encode($response);
    die();
  }

  private function channels_view() {
    $current_url = admin_url('admin.php?page=slack_liveblog_channels');
    $settings_url = admin_url('admin.php?page=slack_liveblog_settings');

    Templates::load_template('channels', [
      'channels' => AdminCore::$channels->get_channels(),
      'slack_home_path' => PluginSettings::i()->get('slack_liveblog_checkbox_field_team_home'),
      'current_url' => $current_url,
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

  private function toggle_channel($id) {
    $channel = Db::i()->get_row('channels', ['closed'], ['id' => $id]);
    $new_status = $channel->closed === '1' ? '0' : '1';

    return Db::i()->update_row('channels', ['closed' => $new_status], ['id' => $id]);
  }

  private function update_refresh_interval($id, $refresh_interval) {
    return Db::i()->update_row('channels', ['refresh_interval' => $refresh_interval], ['id' => $id]);
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
    $workspace = Db::i()->get_row('workspaces', ['*'], ['id' => $_GET['workspace_id']]);

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
    $response_body = json_decode($response['body']);

    if ($response_body->ok === true) {
      Db::i()->update_row('workspaces', ['access_token' => $response_body->access_token], ['id' => $workspace->id]);
    }
  }
}
