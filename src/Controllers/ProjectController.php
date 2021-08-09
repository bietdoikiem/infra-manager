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
  public function index(int $size = 10, int $page = 1) {
    // Read all projects
    $result = $this->projectService->read_projects_bigquery($size, $page);
    $project_list = $result[0];
    $count_project_list = count($project_list);
    $total_result = $result[1];
    // Render page
    View::renderTemplate('Project/index.html', [
      'project_list' => $project_list,
      'total_result' => $total_result,
      'count_project_list' => $count_project_list
    ]);
  }

  /**
   * BigQuery Index page
   * 
   * @param int $size Total rows display in a single page
   * @param int $page Current page number
   * @return void
   */
  public function bigquery_index(int $size = 10, int $page = 1) {
    // Read all projects
    $result = $this->projectService->read_projects_bigquery($size, $page);
    $project_list = $result[0];
    $count_project_list = count($project_list);
    $total_result = $result[1];
    // Render page
    View::renderTemplate('Project/bigquery_index.html', [
      'project_list' => $project_list,
      'total_result' => $total_result,
      'count_project_list' => $count_project_list
    ]);
  }

  /**
   * Index page
   */
  public function search(string $keyword = "", string $country = "", int $size = 10, int $page = 1) {
    // Read all projects
    $result = $this->projectService->search_projects_bigquery($keyword, $country, $size, $page);
    $project_list = $result[0];
    $count_project_list = count($project_list);
    $total_result = $result[1];
    // Render page
    View::renderTemplate('Project/search_index.html', [
      'project_list' => $project_list,
      'total_result' => $total_result,
      'count_project_list' => $count_project_list
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
    $result = $this->projectService->add_project(
      $project_name,
      $subtype,
      $current_status,
      $capacity_mw,
      $year_of_completion,
      $country_list_of_sponsor_developer,
      $sponsor_developer_company,
      $country_list_of_lender_financier,
      $lender_financier_company,
      $country_list_of_construction_epc,
      $construction_company_epc_participant,
      $country,
      $province_state,
      $district,
      $tributary,
      $latitude,
      $longitude,
      $proximity,
      $avg_annual_output_mwh,
      $data_source,
      $announce_more_information,
      $link,
      $latest_update
    );
    if ($result) {
      ServerLogger::log("=> Inserted Project {$result->id} successfully!");
      $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
      header("Location: $location");
      exit;
    }
    $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
    header("Location: $location");
    exit;
  }

  public function edit(
    int $id,
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
    ServerLogger::log("Edit Form posted (project's id): " . $id);
    $result = $this->projectService->edit_project(...func_get_args());
    if ($result) {
      ServerLogger::log("=> Edited Project {$result->id} successfully!");
      $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
      header("Location: $location");
      exit;
    }
    $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
    header("Location: $location");
    exit;
  }

  public function delete(
    int $id
  ) {
    ServerLogger::log("Edit Form posted (project's id): " . $id);
    $result = $this->projectService->delete_project($id);
    if ($result) {
      ServerLogger::log("=> Deleted Project {$id} successfully!");
      $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
      header("Location: $location");
      exit;
    }
    ServerLogger::log("=> Deleted Project {$id} UNSuccessfully!!!");
    $location = getenv("DEPLOY_URL") ? getenv("DEPLOY_URL") : "http://localhost:8080";
    header("Location: $location");
    exit;
  }
}
