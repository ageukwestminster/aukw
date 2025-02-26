<?php

namespace Models;

use Core\QuickbooksConstants as QBO;
use QuickBooksOnline\API\Exception\SdkException;

/**
 * Factory class that provides a method to query QBO entities
 * 
 * @category Model
 */
class QuickbooksQuery{

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;

  /**
   * Private realmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
  }

  /**
   * realmID getter.
   */
  public function getrealmId() : string {
      return $this->realmid;
  }

  /**
   * Constructor
   */
  protected function __construct(){}

  /**
   * Static constructor / factory
   */
  public static function getInstance() {
    return new self();
  }


    /**
   * Return an array of QBO entities whose DocNumber starts with the provided string
   * 
   * More information: {@link https://developer.intuit.com/app/developer/qbo/docs/learn/explore-the-quickbooks-online-api/data-queries}
   * @param string $entity_type_name The QBO entity type name e.g. 'Bill' or 'JournalEntry'
   * @param string $doc_number The returned array of entities will have a DocNumber starting with this string
   * @return array Returns an array of entities that match the doc_number criterion
   */
  public function query_by_docnumber(string $entity_type_name, string $doc_number):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }

    $entities = $dataService->Query("SELECT * FROM " . $entity_type_name 
        ." WHERE DocNumber LIKE '" . $doc_number . "%'");
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else if ($entities) {
      return $entities;
    }

    return [];
    
  }

  /**
   * Return an array of QBO attachments
   * 
   * More information: {@link https://developer.intuit.com/app/developer/qbo/docs/workflows/attach-images-and-notes}
   * @param string $entity_type_name The QBO entity type name e.g. 'Bill' or 'JournalEntry'
   * @param int $qb_txn_id The transaction id of the entity that we are querying
   * @return array Returns an array of attachments
   */
  public function list_attachments(string $entity_type_name, string $qb_txn_id):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      return [];
    }

    //$query = "SELECT Id,FileName,FileAccessUri,TempDownloadUri,Size,ContentType FROM attachable 
    $query = "SELECT * FROM attachable 
                WHERE AttachableRef.EntityRef.Type = '" . $entity_type_name  
                ."' AND AttachableRef.EntityRef.value = '" . $qb_txn_id . "'";

    $attachments = $dataService->Query($query);
    $error = $dataService->getLastError();
    if ($error) {
        throw new SdkException("The Response message is: " . $error->getResponseBody());
    }   
    else if ($attachments) {
      return $attachments;
    }

    return [];
    
  }  

  /**
   * Download QBO attachments to the downloads folder
   * 
   * More information: {@link https://developer.intuit.com/app/developer/qbo/docs/workflows/attach-images-and-notes}
   * @param string $entity_type_name The QBO entity type name e.g. 'Bill' or 'JournalEntry'
   * @param int $qb_txn_id The transaction id of the entity that we are querying
   * @return array Returns an array of attachments
   */
  public function download_attachments(string $entity_type_name, string $qb_txn_id) : array{

    $filenames = array();

    $attachments = $this->list_attachments($entity_type_name, $qb_txn_id);
    
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
      $filePath = $downloads_dir . $attachment->FileName;

      file_put_contents($filePath , file_get_contents($attachment->TempDownloadUri));

      array_push($filenames, $filePath);
    }
    
    return $filenames;
  }  
}