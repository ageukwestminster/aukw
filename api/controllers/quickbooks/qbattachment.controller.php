<?php

namespace Controllers;

use Core\QuickbooksConstants as QBO;
use InvalidArgumentException;
use Models\QuickbooksQuery;
use Models\QuickbooksAttachment;
use QuickBooksOnline\API\Exception\SdkException;

/**
 * Controller to accomplish QBO Attachment related tasks, such as listing
 * attachments for a given entity, uploading and downloading attachments
 * and appending attachments to exisiting entities.
 *
 * @category  Controller
*/
class QBAttachmentCtl{

  /**
   * Query QBO for a list of Attachments for a given entity
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_one_by_id(string $realmid):void{  

    try {
      if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $entity_type_name = $_GET['entity_type'];
      } else {
        throw new \Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new \Exception('Missing txn_id http parameter');
      }
      $attachments = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->list_attachments($entity_type_name, $qb_txn_id);

      echo json_encode(array_values($attachments)); 
    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to download attachments. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }

  /**
   * Download QBO attachments to the downloads folder, for a given entity
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function download(string $realmid):void{  

    try {
      if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $entity_type_name = $_GET['entity_type'];
      } else {
        throw new \Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new \Exception('Missing txn_id http parameter');
      }
      $attachments = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->list_attachments($entity_type_name, $qb_txn_id);

      $filenames = array();
      $downloads_dir = \Core\Config::read('file.downloaddir') ?? "./downloads/";

      // Clean download directory, by deleting every file, except index.html
      // 'grep' code from https://stackoverflow.com/a/12284228/6941165
      $files = preg_grep('/index\.html$/', glob($downloads_dir.'*'), PREG_GREP_INVERT);
      foreach($files as $file){
        if(is_file($file)) {
            unlink($file);
        }
      }
  
      foreach ($attachments as $attachment) {
        $extension = pathinfo($attachment->FileName, PATHINFO_EXTENSION);
        $filePath = $downloads_dir . strtolower(\Core\GUID::GUIDv4()) . '.' . $extension;
  
        file_put_contents($filePath , file_get_contents($attachment->TempDownloadUri));
  
        $file = array();
        $file['FileName'] = $filePath;
        $file['Category'] = $attachment->Category;
        $file['Tag'] = $attachment->Tag;
        $file['ContentType'] = $attachment->ContentType;
        array_push($filenames, $file);
      }
      
      echo json_encode($filenames);

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to download attachments. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }  

  /**
   * Download QBO attachments to the downloads folder, for a given entity
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function upload(string $realmid):void{  

    try {
      $files = json_decode(file_get_contents("php://input"));

      if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $entity_type_name = $_GET['entity_type'];
      } else {
        throw new \Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new \Exception('Missing txn_id http parameter');
      }
 
      foreach ($files as $file) {
        QuickbooksAttachment::getInstance()
          ->setRealmID($realmid)
          ->setFileName($file->FileName)
          ->setContentType($file->ContentType)
          ->attach_to_entity($entity_type_name, $qb_txn_id);
      }

    } catch (\Throwable $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to attach attachments. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }  

  /**
   * Download QBO attachments to the downloads folder, for a given entity
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function delete(string $realmid):void{  

    throw new \Exception('Not implemented');
/*
    try {
      if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $entity_type_name = $_GET['entity_type'];
      } else {
        throw new \Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new \Exception('Missing txn_id http parameter');
      }

      $attachments = QuickbooksQuery::getInstance()
      ->setRealmID($realmid)
      ->list_attachments($entity_type_name, $qb_txn_id);

      if (!$attachments || !is_array($attachments) || !count($attachments)) {
        throw new \Exception('No attachments found.');
      }

      $auth = new \Models\QuickbooksAuth();
      $dataService = $auth->prepare($realmid);
      if ($dataService == false) {
        throw new \Exception('Unable to initialize DataService.');
      }

      foreach($attachments as $attachment){
        // This always fails for some reason
        $dataService->Delete($attachment);

        $error = $dataService->getLastError();
        if ($error) {
          throw new SdkException("The Response message is: " . $error->getResponseBody());
      } 
      }
      echo json_encode(
        array(
            "message" => count($attachments)." attachment(s) deleted from Entity. "
            )
      );

    } catch (\Throwable $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to delete attachments. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }*/
  }  

}