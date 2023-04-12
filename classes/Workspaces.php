<?php

namespace SlackLiveblog;

/**
 * Class Workspaces
 *
 * Workspaces related stuff
 *
 * @package SlackLiveblog
 */
class Workspaces {
  private $database;

  public function __construct() {
    global $wpdb;

    $this->database = $wpdb;
    // To get timestamp values in UTC
    $this->database->query('SET time_zone = \'+00:00\';');
  }

  public function get_workspaces() {
    $query = "
      SELECT
        wo.*
      FROM
        {$this->database->prefix}slack_liveblog_workspaces wo
    ";

    return $this->database->get_results($query);
  }
}
