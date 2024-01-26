<?php

namespace SlackLiveblog;

/**
 * Class Db
 * 
 * Singleton class to handle database interactions.
 */
class Db {
  /** @var Db|null Single instance of the Db class. */
  private static $instance = null;

  /** @var \wpdb|null Instance of the WordPress database abstraction class. */
  private $db = null;

  /**
   * Private constructor to prevent creating multiple instances.
   * Sets up the connection with the WordPress database.
   *
   * @return void
   */
  private function __construct() {
    global $wpdb;

    $this->db = $wpdb;
    $this->db->query('SET time_zone = \'+00:00\';');
  }

  /**
   * Get an instance of the Db class.
   * 
   * @return Db Single instance of the Db class.
   */
  public static function i() {
    if (self::$instance === null) {
      self::$instance = new self();
    }

    return self::$instance;
  }

  /**
   * Delete a row from the specified model/table.
   *
   * @param string $model Model/table name.
   * @param string $field Column name to match against.
   * @param mixed $value Value to match against.
   * @return int|false Number of rows affected or false on error.
   */
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

  /**
   * Update a row in the specified model/table.
   *
   * @param string $model Model/table name.
   * @param array $data Associative array of column and value pairs.
   * @param array $where Associative array of WHERE conditions.
   * @return int|false Number of rows affected or false on error.
   */
  public function update_row($model, $data, $where) {
    $result = $this->db->update($this->get_table($model), $data, $where);

    return $result;
  }

  /**
   * Insert a row into the specified model/table.
   *
   * @param string $model Model/table name.
   * @param array $data Associative array of column and value pairs.
   * @return int|false Number of rows affected or false on error.
   */
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

  /**
   * Retrieve multiple rows from the specified model/table.
   *
   * @param string $model Model/table name.
   * @param array $columns Columns to select.
   * @param array $where Associative array to filter the rows.
   * @param string $order ORDER BY clause.
   * @param string $limit LIMIT clause.
   * @return array|object|null Result set or null on error.
   */
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
      $special_values = [];
      $where_values = [];
      foreach ($where as $field => $value) {
        if (is_string($value) && strpos(strtoupper($value), 'SQL_FUNC:') === 0) {
          $func_value = substr($value, 9);
          $placeholder = '##FUNC_' . count($special_values) . '##';
          $special_values[$placeholder] = $func_value;
          $conditions[] = "$field = $placeholder";
        } else {
          $conditions[] = "$field = %s";
          $where_values[] = $value;
        }
      }
      $query .= implode(" AND ", $conditions);
    }

    if (!empty($order)) {
      $query .= " ORDER BY $order";
    }

    if (!empty($limit)) {
      $query .= " LIMIT $limit";
    }

    if (!empty($where_values)) {
      $query = $this->db->prepare($query, $where_values);
    }

    foreach ($special_values as $placeholder => $func_value) {
      $query = str_replace($placeholder, $func_value, $query);
    }

    $result = $this->db->get_results($query);

    return $result;
  }

  /**
   * Retrieve a single row from the specified model/table.
   *
   * @param string $model Model/table name.
   * @param array $columns Columns to select.
   * @param array $where Associative array to filter the rows.
   * @param string $order ORDER BY clause.
   * @return object|false Result row or false if not found.
   */
  public function get_row($model, $columns = ['*'], $where = [], $order = '') {
    $rows = self::get_rows($model, $columns, $where, $order, '1');

    if (count($rows) === 1) {
      return $rows[0];
    } else {
      return false;
    }
  }

  /**
   * Get the ID of the last inserted row.
   *
   * @return int Last inserted row's ID.
   */
  public function get_last_inserted_id() {
    return $this->db->insert_id;
  }

  /**
   * Get the WordPress database abstraction instance.
   *
   * @return \wpdb wpdb instance.
   */
  public function get_db() {
    return $this->db;
  }

  /**
   * Get the full table name with prefix for the specified model.
   *
   * @param string $model Model/table name without prefix.
   * @return string Full table name with prefix.
   */
  private function get_table($model) {
    return $this->db->prefix . "slack_liveblog_$model";
  }
}
