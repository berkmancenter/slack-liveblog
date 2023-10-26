<?php

namespace SlackLiveblog;

class Db {
  private static $instance = null;
  private $db = null;

  private function __construct() {
    global $wpdb;

    $this->db = $wpdb;
    $this->db->query('SET time_zone = \'+00:00\';');
  }

  public static function i() {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  public function delete_row($model, $field = 'id', $value) {
    $query = "
      DELETE FROM
        {$this->get_table($model)}
      WHERE
        $field = %s
    ";

    $query = $this->db->prepare(
      $query,
      [$value]
    );

    $result =  $this->db->query($query);

    return $result;
  }

  public function update_row($model, $data, $where) {
    $result = $this->db->update($this->get_table($model), $data, $where);

    return $result;
  }

  public function insert_row($model, $data) {
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
        $this->get_table($model),
        implode(', ', $columns),
        implode(', ', $value_placeholders)
    );
    $sql = $this->db->prepare($sql, $values);

    foreach ($special_values as $placeholder => $func_value) {
      $sql = str_replace($placeholder, $func_value, $sql);
    }

    $result = $this->db->query($sql);

    return $result;
  }

  public function get_rows($model, $columns = ['*'], $where = [], $order = '', $limit = '') {
    $column_string = implode(", ", $columns);

    $query = "
      SELECT
        {$column_string}
      FROM
        {$this->get_table($model)}
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
      $query = $this->db->prepare($query, $where_args);
    }
    $result = $this->db->get_results($query);

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
    return $this->db->insert_id;
  }

  public function get_db() {
    return $this->db;
  }

  private function get_table($model) {
    return $this->db->prefix . "slack_liveblog_$model";
  }
}
