<?php

namespace App\Controllers;
use App\Utils\View;

class ProjectController {

  /**
   * Index page
   */
  public function index() {
    // Read all projects
    $project_list = array();
    // Render page
    View::renderTemplate('Project/index.html', [
      'project_list' => $project_list
    ]);
  }
}
