<?php

namespace App\Models\Services;

use App\Models\Entities\Project;
use Google\Cloud\Storage\StorageClient;
use App\Utils\BucketConfig;
use App\Utils\ServerLogger;
use App\Utils\MathUtils;
use Exception;

class ProjectService {
  public $storage;

  public function __construct() {
    $this->storage = new StorageClient([
      'keyFile' => json_decode(file_get_contents(dirname(dirname(__DIR__)) . '/Resources/crafty-coral-281804-c3a7354e1ae6.json'), true),
      'projectId' => 'crafty-coral-281804'
    ]);
    $this->storage->registerStreamWrapper();
  }

  /**
   * Read all Employees in Employees.csv
   * 
   * @return array $emp_list -> List of employees
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
      // Empty string to null for object
      // $project = array_map(fn ($v) => $v === '' ? null : $v, $project);
      $project_obj = new Project(...$project);
      array_push($project_list, $project_obj);
    }
    return $project_list;
  }

  /**
   * Add a new project to project.csv
   * 
   * @param string $project_name
   * ... more info params
   * @return Project|null
   */
  public function add_employee(
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

  // ------------ /// ------------Helper Functions---------------/// ------------- //

  /**
   * Read a CSV object on Cloud Storage
   * 
   * @param string $bucketName
   * @param string $objectName
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
   * @param string $bucketName
   * @param string $objectName
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
   * @param string $bucketName
   * @param string $objectName
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
   * @param string $bucketName
   * @param string $objectName
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
   * @param string $uri Link to resource
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
}
