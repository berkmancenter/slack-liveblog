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
    $this->database = Db::i()->get_db();
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
