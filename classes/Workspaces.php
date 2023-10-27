<?php

namespace SlackLiveblog;

/**
 * Class Workspaces
 *
 * Workspaces related stuff.
 *
 * @package SlackLiveblog
 */
class Workspaces {
  /** @var \wpdb|null Instance of the WordPress database abstraction class. */
  private $database = null;

  public function __construct() {
    $this->database = Db::i()->get_db();
  }

  /**
   * Fetches all workspaces from the database.
   *
   * @return array Resulting workspaces.
   */
  public function get_workspaces() {
    $table_name = $this->database->prefix . 'slack_liveblog_workspaces';

    $query = "
      SELECT
        *
      FROM
        {$table_name}
    ";

    return $this->database->get_results($query);
  }
}
