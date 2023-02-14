<?php

namespace SlackLiveblog;

class Templates {
  public static function load_template($name, $variables = []) {
    require_once __DIR__ . "/../templates/$name.php";
  }
}
