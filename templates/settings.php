<div class="wrap">
  <h1>Settings</h1>

  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>Connect new workspace</h2>
      </div>

      <div class="inside">
        <form>
          <table class="form-table">
            <tbody>
              <tr>
                <th>Workspace name</th>
                <td>
                  <input type="text" class="slack-liveblog-settings-workspace-name" data-key="workspace_name" required>
                  <p class="description" id="tagline-description">Name your workspace to easier identify it later when you create a new channel.</p>
                </td>
              </tr>
              <tr>
                <th>Slack configuration access token</th>
                <td>
                  <input type="text" class="slack-liveblog-settings-app-config-token" data-key="access_token" required>
                  <p class="description" id="tagline-description">Get it here <a target="_blank" href="https://api.slack.com/authentication/config-tokens" target="_blank">https://api.slack.com/authentication/config-tokens</a>. Check the "Creating configuration tokens" section and generate it for a workspace you want to connect.</p>
                </td>
              </tr>
            </tbody>
          </table>

          <button
            class="slack-liveblog-button slack-liveblog-ajax-action"
            data-action="connect-workspace"
            data-elements-submit=".slack-liveblog-settings-app-config-token,.slack-liveblog-settings-workspace-name"
            data-success-message="New workspace has been connected successfully."
            data-success-callback="createdWorkspace"
          >
            <img src="<?php echo plugins_url('slack-liveblog/resources/img/slack_logo.svg') ?>">
            Connect new Slack workspace
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<hr>

<div class="wrap">
  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>Connected workspaces</h2>
      </div>

      <div class="inside">
        <?php if (count($variables['workspaces']) > 0): ?>
          <table class="slack-liveblog-workspaces-list wp-list-table widefat fixed striped table-view-list">
            <thead>
              <tr>
                <th>Name</th>
                <th>Connection date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($variables['workspaces'] as $workspace): ?>
                <tr>
                  <td>
                    <?php echo $workspace->name ?>
                  </td>
                  <td>
                    <?php echo $workspace->created_at ?>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        <?php else: ?>
          <p>No workspaces connected yet.</p>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>
