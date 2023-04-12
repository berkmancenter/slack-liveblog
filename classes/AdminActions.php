<?php

namespace SlackLiveblog;

class AdminActions {
  public function slack_liveblog_channels_init() {
    $this->init_regular_actions();
  }

  private function init_regular_actions() {
    switch (@$_REQUEST['action']) {
      case 'channel-new':
        try {
          AdminCore::$channels->create_new_channel();
          $message = 'New channel has been created.';
        } catch (\Throwable $th) {
          $message = 'There has been a problem with creating a new channel.';
        }
        break;
      case 'connect-from-slack':
        var_dump($_REQUEST);
        die();
        break;
    }

    $this->default_view();
  }

  public function ajax_actions() {
    $response = [];

    switch ($_POST['sub_action']) {
      case 'channel-toggle':
        $response = $this->toggle_channel($_POST['id']);
        break;
      case 'update-refresh-interval':
        $response = $this->update_refresh_interval($_POST['id'], $_POST['refresh_interval']);
        break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    die();
  }

  private function default_view() {
    $current_url = admin_url('admin.php?page=slack_liveblog_channels');

    Templates::load_template('channels', [
      'channels' => AdminCore::$channels->get_channels(),
      'slack_home_path' => PluginSettings::i()->get('slack_liveblog_checkbox_field_team_home'),
      'current_url' => $current_url
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
}
