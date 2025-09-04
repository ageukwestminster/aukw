<?php

namespace Controllers;

use Core\QuickbooksConstants as QBO;
use \Core\ErrorResponse as Error;
use Exception;
use InvalidArgumentException;
use Models\QuickbooksQuery;
use Models\QuickbooksAttachment;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Data\IPPAttachable;

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
  public static function read_by_entity(string $realmid):void{  

    try {
      if (isset($_GET['entity_type']) && !empty($_GET['entity_type'])) {
        $entity_type_name = $_GET['entity_type'];
      } else {
        throw new Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new Exception('Missing txn_id http parameter');
      }
      $attachments = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->list_attachments($entity_type_name, $qb_txn_id);

      echo json_encode(array_values($attachments)); 
    } catch (Exception $e) {
      Error::response("Unable to query QBO entity for attachments.", $e);
    }
  }

    /**
   * Query QBO for details of a single attachment, given by id
   *
   * @param string $realmid The company ID for the QBO company.
   * @param string $id The ID of the attachment.
   * @return void Output is echo'd directly to response 
   */
  public static function read_by_id(string $realmid, string $id):void{  

    try {
      $attachment = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->find_attachment($id);

      echo json_encode($attachment[0], JSON_NUMERIC_CHECK); 
    } catch (Exception $e) {
      Error::response("Unable to query QBO entity, with id=$id, for attachments.", $e);
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
        throw new Exception('Missing entity_type http parameter');
      }
      if (isset($_GET['txn_id']) && !empty($_GET['txn_id'])) {
        $qb_txn_id = $_GET['txn_id'];
      } else {
        throw new Exception('Missing txn_id http parameter');
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
        if ($attachment->Category) $file['Category'] = $attachment->Category;
        if ($attachment->Tag) $file['Tag'] = $attachment->Tag;
        $file['ContentType'] = $attachment->ContentType;
        array_push($filenames, $file);
      }
      
      echo json_encode($filenames);

    } catch (Exception $e) {
      Error::response("Unable to download attachments.", $e);
    }
  }  

  /**
   * Create multiple QBO Attachments from the HTTP Post body text.
   * It creates separate attachments for each item in the files array and
   * each item in the notes array.
   * In addition, for each item in the files array, the file will be uploaded to QBO.
   * Format of request body:
   * {
   *    "attachmentRefs": object[],
   *     "attachments": {
   *         "files": object[],
   *         "notes": string[]
   *     }
   * }
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function create(string $realmid):void{  

    try {

      // Check HTTP body data is of the right format
      $data = json_decode(file_get_contents("php://input"));
      if (!$data || !property_exists($data,'attachments') || !is_object($data->attachments)) {
        throw new InvalidArgumentException("Property 'attachments' is missing or is not an object in the POST body.");
      }
      if (!property_exists($data->attachments,'files') && !property_exists($data->attachments,'notes')) {
        throw new InvalidArgumentException("Must provide either 'files' or 'notes' properties in the "
                                                        ."'attachments' object in the POST body.");
      }
      if ((property_exists($data->attachments,'files') && !is_array($data->attachments->files)) 
            || (property_exists($data->attachments,'notes') && !is_array($data->attachments->notes))) {
        throw new InvalidArgumentException("Must provide either 'files' or 'notes' and it must be an array"
                                              ." in the POST body.");
      }
      if (property_exists($data->attachments,'files')) {
        $files = $data->attachments->files;
      } else {
        $files = [];
      }
      if (property_exists($data->attachments,'notes')) {
        $notes = $data->attachments->notes;
      } else {
        $notes = [];
      }
      if (property_exists($data,'attachmentRefs') && is_array($data->attachmentRefs)) {
        $attachmentRefs = $data->attachmentRefs;
      } else {
        $attachmentRefs = [];
      }
 
      $attachments = array();
      
      foreach ($files as $file) {
        $simplifiedQboAttachment = QuickbooksAttachment::getInstance()
          ->setRealmID($realmid)
          ->setFileName($file->FileName)
          ->setAttachmentRefs($attachmentRefs)
          ->setContentType($file->ContentType)
          ->upload(); 
        array_push($attachments, $simplifiedQboAttachment);
      }

      foreach ($notes as $note) {
        $simplifiedQboAttachment = QuickbooksAttachment::getInstance()
          ->setRealmID($realmid)
          ->setNote($note)
          ->setAttachmentRefs($attachmentRefs)
          ->create_note();
        array_push($attachments, $simplifiedQboAttachment);
      }

      echo json_encode($attachments, JSON_NUMERIC_CHECK);    

    } catch (Exception $e) {
      Error::response("Unable to create attachment in QBO.", $e);
    }
  }

}