<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * This is for development purpose ONLY !
 */
final class ServerLogger {

  /**
   * send a log message to the STDOUT stream.
   *
   * @param array<int, mixed> $args
   *
   * @return void
   */
  public static function log(...$args): void {
    foreach ($args as $arg) {
      if (is_object($arg) || is_array($arg) || is_resource($arg)) {
        $output = print_r($arg, true);
      } else {
        $output = (string) $arg;
      }

      fwrite(STDOUT, $output . "\n");
    }
  }
}
