<?php

namespace SlackLiveblog;

class AdminCore {
  public function __construct() {
    // init modules
    new Settings();
  }
}
