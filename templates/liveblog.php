<div id="slack-liveblog-app"></div>

<script>
  window.slack_liveblog_use_websockets = <?php echo $variables['use_websockets'] ?>;
  window.slack_liveblog_ws_url = '<?php echo $variables['ws_url'] ?>';
  window.slack_liveblog_messages_url = '<?php echo $variables['messages_url'] ?>';
  window.slack_liveblog_closed = '<?php echo $variables['channel']->closed ?>';
  window.slack_liveblog_refresh_interval = '<?php echo $variables['channel']->refresh_interval * 1000 ?>';
  window.slack_liveblog_sorting = '<?php echo $variables['channel']->sorting ?>';
</script>
