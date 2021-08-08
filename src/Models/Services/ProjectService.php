<?php

namespace App\Models\Services;

use App\Models\Entities\Project;
use Google\Cloud\Storage\StorageClient;
use App\Utils\BucketConfig;
use App\Utils\ServerLogger;
use App\Utils\MathUtils;
use Google\Cloud\BigQuery\BigQueryClient;

class ProjectService {
  public $storage;
  public $bigQuery;

  public function __construct() {
    // Initialize connection to Cloud Storage
    $this->storage = new StorageClient([
      'keyFile' => json_decode(file_get_contents(dirname(dirname(__DIR__)) . '/Resources/crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => 'crafty-coral-281804'
    ]);
    $this->storage->registerStreamWrapper();
    // Initialize Connection to BigQuery
    $this->bigQuery = new BigQueryClient([
      'keyFile' => json_decode(file_get_contents(dirname(dirname(__DIR__)) . '/Resources/crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => BucketConfig::GCLOUD_PROJECT_ID,
    ]);
  }

  /**
   * Read all Projects in project.csv
   * 
   * @return Project[] $project_list -> List of projects
   */
  public function read_all_projects(): array {
    $project_list = array();
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::PROJECT_FILE, true);
    foreach ($rows_data as &$project) {
      $project[0] = MathUtils::makeInteger($project[0]); // Convert id to int
      $project[4] = MathUtils::makeFloat($project[4]); // Convert capacity to float
      $project[5] = MathUtils::makeInteger($project[5]);
      $project[16] = MathUtils::makeFloat($project[16]);
      $project[17] = MathUtils::makeFloat($project[17]);
      $project[19] = MathUtils::makeFloat($project[19]);
      $project_obj = new Project(...$project);
      array_push($project_list, $project_obj);
    }
    return $project_list;
  }

  /**
   * Read all Projects in BigQuery Dataset (cursor pagination STYLE!)
   * 
   * @return array [0] -> Project list; [1] -> Total results of query
   */
  public function read_projects_bigquery(int $page_size = 10, int $page_no = 1): array {
    $project_list = array();
    $total_offset = ($page_no - 1) * $page_size;
    // Setup query
    $table_id = "crafty-coral-281804.mekong_project_infra.project";
    $query_statement = <<<EOT
    SELECT * 
    FROM `$table_id`
    ORDER BY id ASC
    LIMIT $page_size
    OFFSET {$total_offset}
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $_ => $project) {
      $idx = array_keys($project); // Convert arbitrary key to manual array's number index
      $project[$idx[0]] = MathUtils::makeInteger($project[$idx[0]]); // Convert id to int
      $project[$idx[4]] = MathUtils::makeFloat($project[$idx[4]]); // Convert capacity to float
      $project[$idx[5]] = MathUtils::makeInteger($project[$idx[5]]);
      $project[$idx[16]] = MathUtils::makeFloat($project[$idx[16]]);
      $project[$idx[17]] = MathUtils::makeFloat($project[$idx[17]]);
      $project[$idx[19]] = MathUtils::makeFloat($project[$idx[19]]);
      $project_obj = new Project(...$project);
      array_push($project_list, $project_obj);
    }
    /* Calculate total query results */
    $total_result = $this->count_total_bigquery($table_id);

    /* Aggregate return results for bigquery function */
    $return_result = array();
    array_push($return_result, $project_list, $total_result);
    return $return_result;
  }

  public function count_total_bigquery($table_id): int {
    $query_statement = <<<EOT
    SELECT COUNT(*) total_result 
    FROM `$table_id`;
    EOT;
    // Setup jobs for executing queries
    $jobConfig = $this->bigQuery->query($query_statement);
    $rows_data = $this->bigQuery->runQuery($jobConfig);
    foreach ($rows_data as $row) {
      return $row['total_result'];
    }
    return 0;
  }

  /**
   * Add a new project to project.csv
   * 
   * @param string $project_name
   * ... more info params
   * @return Project|null
   */
  public function add_project(
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
    // Read first to check if match any ID
    $last_id = $this->read_bucket_file(BucketConfig::BUCKET_NAME, BucketConfig::LAST_ID_FILE);
    $insert_id = $last_id + 1; // Increment for new inserted one's id
    ServerLogger::log("=> Performing inserting new employee to CSV file!");
    // Insert new data
    $insert_row = array(
      $insert_id, $project_name,
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
    ServerLogger::log("=> Insert Array: ", $insert_row);
    $result = $this->insert_bucket_csv($insert_row, BucketConfig::BUCKET_NAME, BucketConfig::PROJECT_FILE);
    // Construct Project object to return
    if ($result) {
      $new_project = new Project(...$insert_row);
      // Update the inserted ID
      $this->write_bucket_file($insert_id, BucketConfig::BUCKET_NAME, BucketConfig::LAST_ID_FILE);
      return $new_project;
    }
    return null;
  }

  /**
   * Edit a project in project.csv
   * 
   * @param string $project_name
   * ... more info params
   * @return Project|null
   */
  public function edit_project(
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
    // Read all data first before editing
    $rows_data = $this->read_bucket_csv(BucketConfig::BUCKET_NAME, BucketConfig::PROJECT_FILE, false);
    ServerLogger::log("=> Performing editing new project to CSV file!");
    $list_of_args = func_get_args();
    foreach ($rows_data as &$project) {
      if ($project[0] == $id) {
        for ($i = 1; $i < count($project); $i++) {
          $project[$i] = $list_of_args[$i];
        }
        break;
      }
    }
    $result = $this->write_bucket_csv_multiple($rows_data, BucketConfig::BUCKET_NAME, BucketConfig::PROJECT_FILE);
    // Construct Project object to return
    if ($result) {
      $edited_project = new Project(...func_get_args());
      return $edited_project;
    }
    return null;
  }

  /**
   * Delete a project from project.csv
   * 
   * @param int $id Id of the project
   * @return bool Return true if success otherwise false
   */
  public function delete_project(int $id) {
    ServerLogger::log("=> Deleting line {$id} in project file...");
    $result = $this->delete_bucket_csv_by_line($id, BucketConfig::BUCKET_NAME, BucketConfig::PROJECT_FILE);
    if ($result == False) {
      ServerLogger::log("=> CANNOT Delete a line {$id}. Please check again!");
      return false;
    }
    ServerLogger::log("=> SUCCESSFULLY Delete a line {$id}.");
    return true;
  }
  // ------------ /// ------------Helper Functions---------------/// ------------- //

  /**
   * Read a CSV object on Cloud Storage
   * 
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return array
   */
  public function read_bucket_csv(string $bucketName, string $objectName, bool $skip_header): array {
    ServerLogger::log("=> Performing read a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $rows_data = $this->read_csv($objectURI, $skip_header);
    ServerLogger::log("=> Successfully read {$objectURI}" . PHP_EOL);
    return $rows_data;
  }

  /**
   * Read a file on bucket
   * 
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return string
   */
  public function read_bucket_file(string $bucketName, string $objectName): string {
    // Open a stream in read-only mode
    ServerLogger::log("=> Performing reading file " . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $file_data = $this->read_file($objectURI);
    return $file_data;
  }


  /**
   * Write a file on bucket
   * 
   * @param string $data
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return string
   */
  public function write_bucket_file(string $data, string $bucketName, string $objectName): string {
    // Open a stream in read-only mode
    ServerLogger::log("=> Performing writing file " . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->write_file($data, $objectURI);
    return $result;
  }

  /**
   * Insert to the end of bucket object file CSV
   * 
   * @param array $row Row of data
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return bool
   */
  public function insert_bucket_csv(array $row, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing insert row to a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->insert_csv($row, $objectURI);
    if ($result == false) {
      ServerLogger::log("=> CANNOT write {$objectURI}" . PHP_EOL);
      return $result;
    }
    ServerLogger::log("=> Successfully write {$objectURI}" . PHP_EOL);
    return $result;
  }


  /**
   * Write CSV file on bucket storage with multiple rows at a time
   * 
   * @param array $rows 2D array of CSV rows
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return bool
   */
  public function write_bucket_csv_multiple(array $rows, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing edit row to a CSV file for object " . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->write_csv_multiple($rows, $objectURI);
    if ($result == false) {
      ServerLogger::log("=> CANNOT edit {$objectURI}" . PHP_EOL);
      return $result;
    }
    ServerLogger::log("=> Successfully edit {$objectURI}" . PHP_EOL);
    return true;
  }


  /**
   * Write CSV file on bucket storage with multiple rows at a time
   * 
   * @param mixed $identifier Identifier of row
   * @param string $bucketName Name of Cloud Storage Bucket
   * @param string $objectName Filename of object
   * @return bool Return true if success otherwise false
   */
  public function delete_bucket_csv_by_line($identifier, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing edit row to a CSV file for object " . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $result = $this->delete_csv_by_line($identifier, $objectURI);
    if ($result == false) {
      ServerLogger::log("=> CANNOT delete a line ($identifier) in {$objectURI}" . PHP_EOL);
      return false;
    }
    ServerLogger::log("=> Successfully delete a line ($identifier) in {$objectURI}" . PHP_EOL);
    return true;
  }

  // ------------- Pure files ----------------- //

  /**
   * Read a CSV file
   * 
   * @param string $uri URI to source (Ex for Cloud Storage: gs://bucket/object)
   * @return array $row_list
   */
  public function read_csv(string $uri, bool $skip_header): array {
    $row_list = array();
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "r")) {
      while (!feof($stream)) {
        for ($i = 0; $row = fgetcsv($stream, 10000, ","); ++$i) {
          /* Skip header switch */
          if ($skip_header == False) {
            array_push($row_list, $row);
          } else {
            // print("Skipped")
            $skip_header = False;
            continue;
          }
        }
      }
    }
    fclose($stream);
    return $row_list;
  }



  /**
   * Read a file
   * 
   * @param string $uri Link to resource
   * @return string
   */
  public function read_file(string $uri): string {
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "r")) {
      while (!feof($stream)) {
        $read_str = fread($stream, 1024);
      }
    }
    fclose($stream);
    return $read_str;
  }

  /**
   * Write a file
   * 
   * @param string $data Data to be written
   * @param string $uri Link to resource
   * @param int $byte Length of bytes to be written (default: 1024)
   * @return bool
   */
  public function write_file(string $data, string $uri, int $byte_length = 1024): bool {
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "w")) {
      $result = fwrite($stream, $data, $byte_length);
    }
    fclose($stream);
    return $result;
  }

  /**
   * Insert to the end of CSV file
   * 
   * @param array $row Array of CSV data by columns
   * @param string $uri Link to resource
   * @return bool
   */
  public function insert_csv(array $row, string $uri): bool {
    if ($stream = fopen($uri, "a")) {
      fputcsv($stream, $row, ",");
    } else {
      return false;
    }
    fclose($stream);
    return true;
  }


  /**
   * Write CSV file with multiple rows at a time
   * 
   * @param array $rows 2D array of CSV rows
   * @param string $uri Link to resource
   * @return bool
   */
  public function write_csv_multiple(array $rows, string $uri): bool {
    if ($stream = fopen($uri, "w")) {
      foreach ($rows as $line) {
        fputcsv($stream, $line, ",");
      }
    } else {
      return false;
    }
    fclose($stream);
    return true;
  }

  /**
   * Delete a row in CSV file
   * @param mixed $identifer Any identifier at the first column of CSV row
   * @param string $uri Link to resource
   * @return boolean Return true if success otherwise false
   */
  public function delete_csv_by_line($identifier, string $uri) {
    $temp = tempnam(sys_get_temp_dir(), 'TMP_');
    $temp_stream = fopen($temp, "w");
    if ($stream = fopen($uri, "r")) {
      while (($row = fgetcsv($stream, 1000)) !== false) {
        if (reset($row) == $identifier) {
          continue;
        }
        fputcsv($temp_stream, $row);
      }
    } else {
      return false;
    }
    fclose($temp_stream);
    fclose($stream);
    file_put_contents($uri, file_get_contents($temp));
    unlink($temp);
    return true;
  }
}
