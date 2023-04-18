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
            <input type="number" min="1" step="1" value="<?php echo $channel->refresh_interval ?>" class="slack-liveblog-channels-list-refresh-interval slack-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>" data-key="refresh_interval"> sec
          </div>
          <a class="slack-liveblog-ajax-action slack-liveblog-pointer"
            data-action="update-refresh-interval"
            data-success-message="Refresh interval has been saved successfully."
            data-elements-submit=".slack-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>,.slack-liveblog-channels-list-id-<?php echo $channel->id ?>"
          >Save</a>
        </td>
        <td>
          <div>
            <?php echo SlackLiveblog\Helpers::get_bool_yes_no($channel->closed) ?>
          </div>
          <a class="slack-liveblog-ajax-action slack-liveblog-pointer"
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
