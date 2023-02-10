<form action='options.php' method='post'>
  <h2>Slack Liveblog settings</h2>

  <?php
    settings_fields('slack_liveblog_settings_page');
    do_settings_sections('slack_liveblog_settings_page');
    submit_button();
  ?>
</form>

<?php
