<?php

namespace App\Utils;

class MathUtils {
  static function makeFloat($val) {
    return is_numeric($val) ? floatval($val) : null;
  }

  static function makeInteger($val) {
    return is_numeric($val) ? intval($val, 10) : null;
  }
}
