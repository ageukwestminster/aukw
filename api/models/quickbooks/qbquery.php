<?php

namespace Models;

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

    $entities = $dataService->Query("SELECT * FROM " . $entity_type_name 
        ." WHERE DocNumber LIKE '" . $doc_number . "%'");
    $error = $dataService->getLastError();
    if ($error) {
      throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
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

    //$query = "SELECT Id,FileName,FileAccessUri,TempDownloadUri,Size,ContentType FROM attachable 
    $query = "SELECT * FROM attachable 
                WHERE AttachableRef.EntityRef.Type = '" . $entity_type_name  
                ."' AND AttachableRef.EntityRef.value = '" . $qb_txn_id . "'";

    /** @var QuickBooksOnline\API\Data\IPPAttachable[] $attachments */
    $attachments = $dataService->Query($query);
    $error = $dataService->getLastError();
    if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    }   
    else if ($attachments) {
      return $attachments;
    }

    return [];
    
  }  
 
    /**
   * Return an array of QBO attachment, given by id
   * @param string $realmid The company ID for the QBO company.
   * @param string $id The ID of the attachment.
   * @return array Returns an array of attachments
   */
  public function find_attachment(string $id):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    //$query = "SELECT Id,FileName,FileAccessUri,TempDownloadUri,Size,ContentType FROM attachable 
    $query = "SELECT * FROM attachable 
                WHERE Id = '" . $id  . "'";

    /** @var QuickBooksOnline\API\Data\IPPAttachable[] $attachments */
    $attachment = $dataService->Query($query);
    $error = $dataService->getLastError();
    if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    }   
    else if ($attachment) {
      return $attachment;
    }
    
    return [];
  }

  /**
   * Return an array of QBO tax codes, or a single tax code, if 'id' is supplied
   * More info {@link https://developer.intuit.com/app/developer/qbo/docs/workflows/manage-sales-tax-for-non-us-locales}
   * 
   * @param string $id an optional id of the entity that we are querying
   * @return array Returns an array of taxCodes
   */
  public function list_tax_codes(string $id = ''):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    $query = "SELECT * FROM TaxCode";
    if ($id !== '') $query .= " WHERE Id = '$id'";

    $attachments = $dataService->Query($query);
    $error = $dataService->getLastError();
    if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    }   
    else if ($attachments) {
      return $attachments;
    }

    return [];
    
  } 

  /**
   * Return an array of QBO entities, or a single entity, if 'id' is supplied
   * 
   * @param string $type The name of the IPPEntity type that we are querying
   * @param bool $inActive If false, search only for disabled entities
   * @return array Returns an array of taxCodes
   */
  public function list_entities(string $type, bool $inActive = false):array{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    $query = "SELECT * FROM $type";
    if ($inActive) $query .= " WHERE Active = false";
    $query .= " STARTPOSITION 0 MAXRESULTS 1000";

    $entities = $dataService->Query($query);
    $error = $dataService->getLastError();
    if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    }   
    else if ($entities) {
      return $entities;
    }

    return [];
    
  } 
}