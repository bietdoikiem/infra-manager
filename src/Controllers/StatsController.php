<?php

namespace App\Controllers;

use App\Utils\View;
use App\Models\Services\StatsService;

class StatsController {
  public $statsService;

  public function __construct(StatsService $statsService) {
    $this->statsService = $statsService;
  }

  /**
   * Index page of Statistics
   * 
   * @return void
   */
  public function index() {
    // Render page
    $result = $this->statsService->get_git_stats();
    $total_commit = $result[0];
    $top_languages = $result[1];
    $top_licenses = $result[2];
    $other_license_usage = $result[3];
    View::renderTemplate(
      "Stats/index.html",
      [
        "total_commit" => $total_commit,
        "top_languages" => $top_languages,
        "top_licenses" => $top_licenses,
        "other_license_usage" => $other_license_usage
      ]
    );
  }
}
