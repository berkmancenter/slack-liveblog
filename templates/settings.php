<h2>Slack Liveblog settings</h2>

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
    class="slack-liveblog-connect-button slack-liveblog-channels-list-pointer slack-liveblog-ajax-action"
    data-action="connect-workspace"
    data-elements-submit=".slack-liveblog-settings-app-config-token,.slack-liveblog-settings-workspace-name"
    data-success-message="New workspace has been connected successfully."
    data-success-callback="createdWorkspace"
  >
    <svg xmlns="http://www.w3.org/2000/svg" style="height:16px;width:16px;margin-right:12px" viewBox="0 0 122.8 122.8"><path d="M25.8 77.6c0 7.1-5.8 12.9-12.9 12.9S0 84.7 0 77.6s5.8-12.9 12.9-12.9h12.9v12.9zm6.5 0c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9v32.3c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9V77.6z" fill="#e01e5a"></path><path d="M45.2 25.8c-7.1 0-12.9-5.8-12.9-12.9S38.1 0 45.2 0s12.9 5.8 12.9 12.9v12.9H45.2zm0 6.5c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9H12.9C5.8 58.1 0 52.3 0 45.2s5.8-12.9 12.9-12.9h32.3z" fill="#36c5f0"></path><path d="M97 45.2c0-7.1 5.8-12.9 12.9-12.9s12.9 5.8 12.9 12.9-5.8 12.9-12.9 12.9H97V45.2zm-6.5 0c0 7.1-5.8 12.9-12.9 12.9s-12.9-5.8-12.9-12.9V12.9C64.7 5.8 70.5 0 77.6 0s12.9 5.8 12.9 12.9v32.3z" fill="#2eb67d"></path><path d="M77.6 97c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9-12.9-5.8-12.9-12.9V97h12.9zm0-6.5c-7.1 0-12.9-5.8-12.9-12.9s5.8-12.9 12.9-12.9h32.3c7.1 0 12.9 5.8 12.9 12.9s-5.8 12.9-12.9 12.9H77.6z" fill="#ecb22e"></path></svg>
    Connect new Slack workspace
  </button>
</form>

<h3>Connected Slack workspaces</h3>

<?php if (count($variables['workspaces']) > 0): ?>
  <table class="slack-liveblog-workspaces-list">
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
