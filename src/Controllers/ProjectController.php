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

  public function add(
    string $project_name,
    string $subtype,
    string $current_status,
    ?float $capacity_mw,
    ?int $year_of_completion,
    ?string $country_list_of_sponsor_developer,
    ?string $sponsor_developer_company,
    ?string $country_list_of_lender_financier,
    ?string $lender_financier_company,
    ?string $country_list_of_construction_epc,
    ?string $construction_company_epc_participant,
    string $country,
    ?string $province_state,
    string $district,
    ?string $tributary,
    float $latitude,
    float $longitude,
    ?string $proximity,
    ?float $avg_annual_output_mwh,
    ?string $data_source,
    ?string $announce_more_information,
    ?string $link,
    ?string $latest_update
  ) {
    ServerLogger::log("Form posted (project's name): " . $project_name);
    header("Location: " . getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080");
  }
}
