<div class="wrap">
  <h1>Channels</h1>

  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>New channel</h2>
      </div>

      <div class="inside">
        <?php if (count($variables['workspaces']) > 0): ?>
          <p>
            <form method="post">
              <p>
                Generating a new channel means that a new private channel will be created in Slack and a user from the "Slack member ID" field will be invited to the channel.
              </p>

              <table class="form-table">
                <tbody>
                  <tr>
                    <th>Slack member ID</th>
                    <td>
                      <input type="text" name="user-id" id="user-id" data-key="user-id" required>
                      <p class="description" id="tagline-description">Where to find it? Try this Medium <a target="_blank" href="https://moshfeu.medium.com/how-to-find-my-member-id-in-slack-workspace-d4bba942e38c">article</a>.</p>
                    </td>
                  </tr>
                  <tr>
                    <th>Channel name</th>
                    <td>
                      <input type="text" name="name" id="name" data-key="name" required>
                      <p class="description" id="tagline-description">Channel names have a 21 character limit and can include lowercase letters, non-Latin characters, numbers and hyphens.</p>
                    </td>
                  </tr>
                  <tr>
                    <th>Workspace</th>
                    <td>
                      <select name="workspace" id="workspace" data-key="workspace" required>
                      <?php foreach ($variables['workspaces'] as $workspace): ?>
                        <option value="<?php echo $workspace->id ?>"><?php echo $workspace->name ?></option>
                      <?php endforeach ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <th>Refresh interval</th>
                    <td>
                      <input type="number" name="refresh-interval" id="refresh-interval" data-key="refresh-interval" value="3" min="1" required>
                      <p class="description" id="tagline-description">How often messages refresh when users view the channel, in seconds.</p>
                    </td>
                  </tr>
                </tbody>
              </table>

              <input type="hidden" name="action" id="action" value="channel-new">

              <button
                class="slack-liveblog-button slack-liveblog-ajax-action"
                data-action="channel-new"
                data-elements-submit="#user-id,#name,#workspace,#refresh-interval"
                data-success-message="New channel has been created successfully."
                data-success-callback="createdChannel"
              >
                <img src="<?php echo plugins_url('liveblog-with-slack/resources/img/slack_logo.svg') ?>">
                Create new channel
              </button>
            </form>
          </p>
        <?php else: ?>
          <p>
            To add a new channel you need to connect at least one workspace here: <a href="<?php echo $variables['settings_url'] ?>"><?php echo $variables['settings_url'] ?></a>.
          </p>
        <?php endif ?>
      </div>
    </div>
  </div>
</div>

<hr>

<div class="wrap">
  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>Existing channels</h2>
      </div>

      <div class="slack-liveblog-channels-parent inside">
        <?php echo $variables['channels_list'] ?>
      </div>
  </div>
</div>
