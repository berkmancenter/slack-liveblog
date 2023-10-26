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
      // To get timestamp values in UTC
      self::$instance->db->query('SET time_zone = \'+00:00\';');
    }

    return self::$instance;
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

  public function insert_row($model, $data) {
    $prefix = self::i()->db->prefix;
    $table = "{$prefix}slack_liveblog_$model";

    $columns = [];
    $values = [];
    $value_placeholders = [];
    $special_values = [];

    foreach ($data as $key => $value) {
        $columns[] = "`{$key}`";
        if (is_string($value) && strpos(strtoupper($value), 'SQL_FUNC:') === 0) {
            $func_value = substr($value, 9);
            $placeholder = '##FUNC_' . count($special_values) . '##';
            $special_values[$placeholder] = $func_value;
            $value_placeholders[] = $placeholder;
        } else {
            $values[] = $value;
            $value_placeholders[] = '%s';
        }
    }

    $sql = sprintf(
        "INSERT INTO `%s` (%s) VALUES (%s)",
        $table,
        implode(', ', $columns),
        implode(', ', $value_placeholders)
    );
    $sql = self::i()->db->prepare($sql, $values);

    foreach ($special_values as $placeholder => $func_value) {
      $sql = str_replace($placeholder, $func_value, $sql);
    }

    $result = self::i()->db->query($sql);

    return $result;
  }

  public function get_rows($model, $columns = ['*'], $where = [], $order = '', $limit = '') {
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
    $where_args = array_values($where);

    if (!empty($order)) {
      $query .= " ORDER BY $order";
    }

    if (!empty($limit)) {
      $query .= " LIMIT $limit";
    }

    if (!empty($where_args)) {
      $query = self::i()->db->prepare($query, $where_args);
    }
    $result = self::i()->db->get_results($query);

    return $result;
  }

  public function get_row($model, $columns = ['*'], $where = [], $order = '') {
    $rows = self::get_rows($model, $columns, $where, $order, '1');

    if (count($rows) === 1) {
      return $rows[0];
    } else {
      return false;
    }
  }

  public function get_last_inserted_id() {
    return self::i()->db->insert_id;
  }
}
