<h3 class="slack-liveblog-channel-title">Liveblog</h3>

<div class="slack-liveblog-messages">
  <?php foreach ($variables['messages'] as $message): ?>
    <div class="slack-liveblog-messages-item-parent">
      <div class="slack-liveblog-messages-item-author">
        <?php echo $message->name; ?>
      </div>
      <div class="slack-liveblog-messages-item-body">
        <?php echo $message->message; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
