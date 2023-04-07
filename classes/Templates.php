<?php

namespace SlackLiveblog;

class Templates {
  public static function load_template($name, $variables = [], $return_string = false) {
    if ($return_string === false) {
      require_once __DIR__ . "/../templates/$name.php";
    } else {
      ob_start();

      require_once __DIR__ . "/../templates/$name.php";

      $output = ob_get_contents();
      ob_end_clean();

      return $output;
    }
  }
}
