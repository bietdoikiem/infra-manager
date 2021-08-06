<?php

namespace App\Controllers;

use App\Utils\View;
use App\Models\Services\ProjectService;
use App\Utils\ServerLogger;

class ProjectController {

  public $projectService;

  public function __construct(ProjectService $projectService) {
    $this->projectService = $projectService;
  }

  /**
   * Index page
   */
  public function index() {
    // Read all projects
    $project_list = $this->projectService->read_all_projects();
    // Render page
    View::renderTemplate('Project/index.html', [
      'project_list' => $project_list
    ]);
  }

  public function add() {
    ServerLogger::log("Form posted");
    header("Location: " . getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080");
  }
}
