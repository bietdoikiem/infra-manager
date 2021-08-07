<?php

namespace App\Utils;

class FilterUtils {
  public static function mask_empty(string $var) {
    return (empty($var) || ctype_space($var)) ? NULL : $var;
  }
}

// $post = array_filter($_POST, 'drop_empty');
