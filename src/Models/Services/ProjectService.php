<?php

namespace App\Models\Services;

use Google\Cloud\Storage\StorageClient;
use App\Utils\BucketConfig;
use App\Utils\ServerLogger;

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
   * Read a CSV object on Cloud Storage
   * 
   * @param string $bucketName
   * @param string $objectName
   * @return array $rows_data
   */
  public function read_bucket_csv(string $bucketName, string $objectName, bool $skip_header): array {
    ServerLogger::log("=> Performing read a CSV file for object" . $objectName);
    $objectURI = "gs://{$bucketName}/{$objectName}";
    $rows_data = $this->read_csv($objectURI, $skip_header);
    ServerLogger::log("=> Successfully read {$objectURI}" . PHP_EOL);
    return $rows_data;
  }

  /**
   * Insert to the end of bucket object file CSV
   * 
   * @param array $row -> Row of data
   * @param string $bucketName
   * @param string $objectName
   * @return bool $result
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
   * @param array $rows -> 2D array of CSV rows
   * @param string $uri -> Link to resource
   * @return bool true/false
   */
  public function write_bucket_csv_multiple(array $rows, string $bucketName, string $objectName): bool {
    ServerLogger::log("=> Performing edit row to a CSV file for object" . $objectName);
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
   * Read an object on Cloud Storage
   * @return undefined
   */
  // public function read_bucket_object($bucketName, $objectName) {
  //   ServerLogger::log("=> Performing read for object " . $objectName);
  //   $objectURI = "gs://{$bucketName}/{$objectName}";
  //   ServerLogger::log('=> Successfully read gs://%s//%s' . PHP_EOL, $bucketName, $objectName);
  //   return;
  // }

  /**
   * Read a CSV file
   * 
   * @param string $uri -> URI to source (Ex for Cloud Storage: gs://bucket/object)
   * @return array $row_list -> List of rows
   */
  public function read_csv(string $uri, bool $skip_header): array {
    $row_list = array();
    // Open a stream in read-only mode
    if ($stream = fopen($uri, "r")) {
      while (!feof($stream)) {
        for ($i = 0; $row = fgetcsv($stream, 1000, ","); ++$i) {
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
   * @param string $uri -> link to resource
   * @return string $read_str
   */
  public function read_file(string $uri): array {
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
   * Insert to the end of CSV file
   * 
   * @param array $row -> Array of CSV data by columns
   * @param string $uri -> Link to resource
   * @return bool true/false
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
   * @param array $rows -> 2D array of CSV rows
   * @param string $uri -> Link to resource
   * @return bool true/false
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
