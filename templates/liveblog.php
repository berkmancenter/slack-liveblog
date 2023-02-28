<div id="slack-liveblog-app"></div>

<script>
  window.slack_liveblog_ws_url = '<?php echo $variables['ws_url'] ?>';
  window.slack_liveblog_messages_url = '<?php echo $variables['messages_url'] ?>';
  window.slack_liveblog_closed = '<?php echo $variables['channel']->closed ?>';
</script>
