<?php

namespace App\Models\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use App\Utils\BucketConfig;


class StatsService {
  public $bigQuery;

  public function __construct() {
    // Initialize Connection to BigQuery
    $this->bigQuery = new BigQueryClient([
      'keyFile' => json_decode(file_get_contents(dirname(dirname(__DIR__)) . '/Resources/crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => BucketConfig::GCLOUD_PROJECT_ID,
    ]);
  }

  /**
   * Get the Git Statistics
   * 
   * @return array Return result list: \
   * [0] -> Total commits in Github (int) \
   * [1] -> Top 10 languages (language_name, total_result) \
   * [2] -> Top 5 licenses (license, license_usage) \
   * [3] -> Other license usage (int)
   */
  public function get_git_stats(): array {
    $return_results = array();
    $total_commits = $this->get_total_commits_by_period(1609459200);
    $top_10_languages = $this->get_top_languages(10);
    $top_5_licenses = $this->get_top_licenses(5);
    $total_license_usage = $this->get_total_licenses_usage();
    $other_licenses_usage = $total_license_usage - array_sum($top_5_licenses);
    array_push($return_results, $total_commits, $top_10_languages, $top_5_licenses, $other_licenses_usage);
    return $return_results;
  }

  /**
   * Get top [n] programming languages
   * 
   * @param int $top Specific [n] top languages
   * @return array Return rows of data in which 1st index is language_name, \
   * 2nd index is total_result
   */
  public function get_top_languages(int $top = 10): array {
    $return_rows = array();
    $query_statement = <<<EOT
    SELECT l.name language_name, COUNT(l.name) total_result
    FROM `bigquery-public-data.github_repos.languages`, UNNEST(language) as l
    GROUP BY l.name
    ORDER BY total_result DESC
    LIMIT $top
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $row) {
      array_push($return_rows, $row);
    }
    return $return_rows;
  }

  /**
   * Get top [n] software product licenses
   * 
   * @param int $top Specific [n] top licenses used
   * @return array Return rows of data in which 1st index is license, \
   * 2nd index is license_usage
   */
  public function get_top_licenses(int $top = 5): array {
    $return_rows = array();
    $query_statement = <<<EOT
    SELECT license, COUNT(license) license_usage
    FROM `bigquery-public-data.github_repos.licenses`
    GROUP BY license
    ORDER BY license_usage DESC
    LIMIT $top
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $row) {
      array_push($return_rows, $row);
    }
    return $return_rows;
  }

  /**
   * Get total usage of software licenses
   * 
   * @return int Total results / Returns 0 if not any
   */
  public function get_total_licenses_usage(): int {
    $query_statement = <<<EOT
    SELECT
    SUM(total_table.total_usage) AS total
    FROM (
    SELECT
      COUNT(*) total_usage
    FROM
      `bigquery-public-data.github_repos.licenses`
    GROUP BY
      license) AS total_table
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $row) {
      return $row['total'];
    }
    return 0;
  }
  /**
   * Total commits on Github since 2021 (1609459200)
   * 
   * @param int timestamp Timestamp of given period of time
   * @return int Total count of commits for a given period of time
   */
  public function get_total_commits_by_period(int $timestamp = 1609459200): int {
    $query_statement = <<<EOT
    SELECT COUNT(committer) total_count
    FROM `bigquery-public-data.github_repos.commits` 
    WHERE committer.time_sec >= $timestamp;
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $row) {
      return $row['total_count'];
    }
    return 0;
  }
}
