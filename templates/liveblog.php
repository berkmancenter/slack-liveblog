<h2 class="slack-liveblog-channel-title">Liveblog</h2>

<div class="slack-liveblog-messages">
  <?php foreach ($variables['messages'] as $index => $message): ?>
    <div class="slack-liveblog-messages-item-parent" data-id="<?php echo $message->id ?>">
      <div class="slack-liveblog-messages-item-header">
        <div class="slack-liveblog-messages-item-author">
          <?php
            $previous_message = $variables['messages'][$index - 1] ??= null;
            if (
                !$previous_message ||
                $previous_message && $previous_message->name != $message->name
               ) {
              echo $message->name;
            }
          ?>
        </div>
        <div class="slack-liveblog-messages-item-time">
          <?php echo $message->created_at ?>
        </div>
      </div>
      <div class="slack-liveblog-messages-item-body">
        <?php echo $message->message ?>
      </div>
    </div>
  <?php endforeach ?>
</div>

<script>
  let slack_liveblog_ws_url = '<?php echo $variables['ws_url'] ?>';
</script>
