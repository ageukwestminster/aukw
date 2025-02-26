<?php

namespace Models;

use QuickBooksOnline\API\Data\IPPReferenceType;
use QuickBooksOnline\API\Data\IPPAttachableRef;
use QuickBooksOnline\API\Data\IPPAttachable;
use QuickBooksOnline\API\Exception\SdkException;

/**
 * Factory class that provides a method to upload attachments
 * 
 * @category Model
 */
class QuickbooksAttachment{

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;
  /**
   * The type of attachment, like 'Image' or 'Document'
   *
   * @var string
   */
  protected string $category;
  /**
   * A tag on the attachment
   *
   * @var string
   */
  protected string $tag;
  /**
   * A note on the attachment
   *
   * @var string
   */
  protected string $note;
  /**
   * Size of the attachment
   *
   * @var int
   */
  protected int $size;
  /**
   * ContentType of the attachment, e.g. 'application/pdf'
   *
   * @var string
   */
  protected string $contentType;
  /**
   * Id of the uploaded attachment
   *
   * @var string
   */
  protected string $id;
    /**
   * SyncToken of the uploaded attachment
   *
   * @var string
   */
  protected string $syncToken;
    /**
   * FileName of the attachment. Max Length: 1000
   *
   * @var string
   */
  protected string $fileName;

  /**
   * Private realmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
  }

  /**
   * Private Category setter.
   */
  public function setCategory(string $category) {
    $this->category = $category;
    return $this;
  }

  /**
   * Private Document Id setter.
   */
  public function setId(string $id) {
    $this->id = $id;
    return $this;
  }
  /**
   * Private file size setter.
   */
  public function setSize(string $size) {
    $this->size = $size;
    return $this;
  }
  /**
   * Private file name setter. Max size 1000 characters.
   */
  public function setFileName(string $fileName) {
    $this->fileName = $fileName;
    return $this;
  }
  /**
   * Private tag setter.
   */
  public function setTag(string $tag) {
    $this->tag = $tag;
    return $this;
  }
    /**
   * Private Content Type setter.
   */
  public function setContentType(string $contentType) {
    $this->contentType = $contentType;
    return $this;
  }
      /**
   * Private SyncToken setter.
   */
  public function setSyncToken(string $syncToken) {
    $this->syncToken = $syncToken;
    return $this;
  }
  /**
   * ContentType getter.
   */
  public function getContentType() : string {
      return $this->contentType;
  }
  /**
   * File name getter.
   */
  public function getFileName() : string {
      return $this->fileName;
  }
  /**
   * File size getter.
   */
  public function getSize() : string {
      return $this->size;
  }

    /**
   * File size getter.
   */
  public function getSyncToken() : string {
    return $this->syncToken;
}

    /**
   * Document Id getter.
   */
  public function getId() : string {
    return $this->id;
}

  /**
   * Tag getter.
   */
  public function getTag() : string {
      return $this->tag;
  }

  /**
   * Category getter.
   */
  public function getCategory() : string {
      return $this->category;
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
   * Attach a file to an entity
   * 
   * @param string $entity_type_name The QBO entity type name e.g. 'Bill' or 'JournalEntry'
   * @param int $qb_txn_id The transaction id of the entity that we are querying
   * @return 
   */
  public function attach_to_entity(string $entity_type_name, string $qb_txn_id) : QuickbooksAttachment{

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);
    if ($dataService == false) {
      throw new \Exception('Unable to initialize DataService.');
    }

    $entityRef = new IPPReferenceType(array('value'=>$qb_txn_id, 'type'=>$entity_type_name));
    $attachableRef = new IPPAttachableRef(array('EntityRef'=>$entityRef));
    $objAttachable = new IPPAttachable();
    $objAttachable->FileName = pathinfo($this->fileName, PATHINFO_BASENAME);
    $objAttachable->AttachableRef = $attachableRef;

    // From https://stackoverflow.com/a/13758760/6941165
    $data = file_get_contents($this->fileName);
    
    /** @var IPPAttachableResponse $resultObj */
    $resultObj = $dataService->Upload(base64_encode($data),
                        $objAttachable->FileName,
                        $this->contentType,
                        $objAttachable);

    if (property_exists($resultObj, 'Fault') && $resultObj->Fault &&
                    property_exists($resultObj->Fault, 'Error')) {
      throw new SdkException($resultObj->Fault->Error->Detail);
    }
    
    if (property_exists($resultObj, 'Attachable') && $resultObj->Attachable) {
      /** @var IPPAttachable $attachment */
      $attachment = $resultObj->Attachable;
      $this->id = $attachment->Id;
      $this->syncToken = $attachment->SyncToken;
      $this->size = $attachment->Size;
      $this->fileName = $attachment->FileName;
      return $this;
    } else {
      throw new \Exception('Unknown object type returned from upload response.');
    }
  }

}