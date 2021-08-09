<?php

namespace App\Utils;

use DateTime;

class DateUtils {
  public static function date_to_string(DateTime $date) {
    return $date->format('m/d/Y');
  }

  public static function string_to_date(string $date_string) {
    $date = new DateTime($date_string);
    return $date;
  }
}
