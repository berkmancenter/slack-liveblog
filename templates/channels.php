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
                      <input type="text" name="user-id" id="user-id" required>
                      <p class="description" id="tagline-description">Where to find it? Try this Medium <a target="_blank" href="https://moshfeu.medium.com/how-to-find-my-member-id-in-slack-workspace-d4bba942e38c">article</a>.</p>
                    </td>
                  </tr>
                  <tr>
                    <th>Channel name</th>
                    <td>
                      <input type="text" name="name" id="name" required>
                      <p class="description" id="tagline-description">Channel names have a 21 character limit and can include lowercase letters, non-Latin characters, numbers and hyphens.</p>
                    </td>
                  </tr>
                  <tr>
                    <th>Workspace</th>
                    <td>
                      <select name="workspace" id="workspace" required>
                      <?php foreach ($variables['workspaces'] as $workspace): ?>
                        <option value="<?php echo $workspace->id ?>"><?php echo $workspace->name ?></option>
                      <?php endforeach ?>
                      </select>
                    </td>
                  </tr>
                  <tr>
                    <th>Refresh interval</th>
                    <td>
                      <input type="number" name="refresh-interval" id="refresh-interval" value="3" min="1" required>
                      <p class="description" id="tagline-description">How often messages refresh when users view the channel, in seconds.</p>
                    </td>
                  </tr>
                </tbody>
              </table>

              <input type="hidden" name="action" id="action" value="channel-new">
              <input type="submit" name="generate-new-channel" id="generate-new-channel" class="button button-primary" value="Generate new channel">
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

      <div class="inside">
        <table class="slack-liveblog-channels-list wp-list-table widefat fixed striped table-view-list">
          <thead>
            <tr>
              <th>Name</th>
              <th>Workspace</th>
              <th>Slack url</th>
              <th>Owner ID</th>
              <th>Tag</th>
              <th>Refresh interval</th>
              <th>Closed</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($variables['channels'] as $channel): ?>
              <?php
                $slack_channel_url = "https://app.slack.com/client/{$channel->workspace_team_id}/{$channel->slack_id}";
              ?>
              <tr data-id="<?php echo $channel->id ?>">
                <td><?php echo $channel->name ?></td>
                <td><?php echo $channel->workspace_name ?></td>
                <td>
                  <a target="_blank" href=<?php echo $slack_channel_url ?>>
                    <?php echo $slack_channel_url ?>
                  </a>
                </td>
                <td><?php echo $channel->owner_id ?></td>
                <td>
                  <input type="hidden" class="slack-liveblog-channels-list-id-<?php echo $channel->id ?>" value="<?php echo $channel->id ?>" data-key="id">
                  [slack_liveblog channel_id="<?php echo $channel->slack_id ?>"/]
                </td>
                <td>
                  <div>
                    <input type="number" min="1" value="<?php echo $channel->refresh_interval ?>" class="slack-liveblog-channels-list-refresh-interval slack-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>" data-key="refresh_interval"> sec
                  </div>
                  <a class="slack-liveblog-ajax-action slack-liveblog-channels-list-pointer"
                    data-action="update-refresh-interval"
                    data-success-message="Refresh interval has been saved successfully."
                    data-elements-submit=".slack-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>,.slack-liveblog-channels-list-id-<?php echo $channel->id ?>"
                  >Save</a>
                </td>
                <td>
                  <div>
                    <?php echo SlackLiveblog\Helpers::get_bool_yes_no($channel->closed) ?>
                  </div>
                  <a class="slack-liveblog-ajax-action slack-liveblog-channels-list-pointer"
                    data-action="channel-toggle"
                    data-success-message="Closed status has been saved successfully."
                    data-success-callback="closedChange"
                    data-elements-submit=".slack-liveblog-channels-list-status-<?php echo $channel->id ?>,.slack-liveblog-channels-list-id-<?php echo $channel->id ?>"
                  ><?php echo SlackLiveblog\Helpers::get_channel_open_close($channel->closed) ?></a>
                  <input type="hidden" class="slack-liveblog-channels-list-status-<?php echo $channel->id ?>" value="<?php echo !$channel->closed ?>" data-key="status">
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
  </div>
</div>
