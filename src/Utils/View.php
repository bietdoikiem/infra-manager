<?php

namespace App\Utils;

class View {
  /**
   * Render a view using template with Twig
   * 
   * @param string $template The template file
   * @param array $args Associative array of data to display in the view (optional)
   * 
   * @return void
   */
  public static function renderTemplate($template, $args = []): void {
    static $twig = null;

    if ($twig === null) {
      $loader = new \Twig\Loader\FilesystemLoader(dirname(__DIR__) . '/Views');
      $twig = new \Twig\Environment($loader);
    }

    echo $twig->render($template, $args);
  }
}
