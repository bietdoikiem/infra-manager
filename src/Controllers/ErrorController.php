<?php
namespace App\Controllers;
use App\Utils\View;

class ErrorController {

  public function error() {
    View::renderTemplate("404.html");
  }
}
