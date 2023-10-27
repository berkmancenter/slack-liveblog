<?php

namespace SlackLiveblog;

/**
 * Class Templates
 *
 * Manages template loading and rendering.
 *
 * @package SlackLiveblog
 */
class Templates {
 /**
  * Loads and renders a template file.
  *
  * @param string $name Name of the template file.
  * @param array $variables Variables to be passed to the template.
  * @param bool $return_string Whether to return the output as a string or to echo it.
  * @return string|null Output of the template if $return_string is true, otherwise null.
  */
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
