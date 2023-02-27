<?php

namespace SlackLiveblog;

class Db {
  private static $instance = null;
  private $db = null;

  public static function i() {
    if (self::$instance === null) {
      global $wpdb;

      self::$instance = new Db();
      self::$instance->db = $wpdb;
    }

    return self::$instance;
  }

  public function get_row($model, $field = 'id', $value) {
    $prefix = self::i()->db->prefix;
    $query = "
      SELECT
        *
      FROM
        {$prefix}slack_liveblog_$model
      WHERE
        $field = %s
    ";

    $query = self::i()->db->prepare(
      $query,
      [$value]
    );

    $row =  self::i()->db->get_row($query);

    return $row;
  }

  public function delete_row($model, $field = 'id', $value) {
    $prefix = self::i()->db->prefix;
    $query = "
      DELETE FROM
        {$prefix}slack_liveblog_$model
      WHERE
        $field = %s
    ";

    $query = self::i()->db->prepare(
      $query,
      [$value]
    );

    $result =  self::i()->db->query($query);

    return $result;
  }

  public function update_row($model, $data, $where) {
    $prefix = self::i()->db->prefix;
    $table = "{$prefix}slack_liveblog_$model";

    $result = self::i()->db->update($table, $data, $where);

    return $result;
  }

  public function get_rows($model, $columns = ['*'], $where = []) {
    $prefix = self::i()->db->prefix;
    $column_string = implode(", ", $columns);

    $query = "
      SELECT
        {$column_string}
      FROM
        {$prefix}slack_liveblog_{$model}
    ";

    if (!empty($where)) {
      $query .= "WHERE ";
      $conditions = [];
      foreach ($where as $field => $value) {
        $conditions[] = "$field = %s";
      }
      $query .= implode(" AND ", $conditions);
    }

    $args = array_values($where);

    $query = self::i()->db->prepare($query, $args);
    $result = self::i()->db->get_results($query);

    return $result;
  }
}
