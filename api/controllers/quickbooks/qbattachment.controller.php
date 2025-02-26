<?php

namespace Controllers;

use Core\QuickbooksConstants as QBO;
use InvalidArgumentException;
use Models\QuickbooksQuery;

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
  public static function list_attachments(string $realmid):void{  

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
   * Query QBO for a list of Attachments for a given entity
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function download_attachments(string $realmid):void{  

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
      echo json_encode(QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->download_attachments($entity_type_name, $qb_txn_id));

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to list attachments. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
    }
  }  
}