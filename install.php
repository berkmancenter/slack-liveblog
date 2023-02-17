<?php

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function slack_liveblog_install() {
  $db_version = 1.0;
  update_option('slack_liveblog_version', $db_version);
}
