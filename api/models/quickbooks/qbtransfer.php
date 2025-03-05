<?php

namespace Models;

use QuickBooksOnline\API\Facades\Transfer;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Data\IPPIntuitEntity;

/**
 * Factory class that provides data about QBO Transfers.
 * 
 * @category Model
 */
class QuickbooksTransfer{
 
  /**
   * The QBO id of the Quickbooks Transfer.
   *
   * @var string
   */
  protected string $id;

    /**
   * The QBO sync token of the Quickbooks Transfer.
   *
   * @var int
   */
  protected int $syncToken;

  /**
   * The QBO company ID
   *
   * @var string
   */
  protected string $realmid;

  /**
   * The transaction date of the Transfer
   *
   * @var string
   */
  protected string $TxnDate;  

  /**
   * The number of the 'from' account
   *
   * @var int
   */
  protected int $fromAccountNo;  

  /**
   * The number of the 'to' account
   *
   * @var int
   */
  protected int $toAccountNo;  
  /**
   * The amount of money to transfer, must be positive and non-zero
   *
   * @var float
   */
  protected float $amount; 
  /**
   * A memo about the transfer
   *
   * @var string
   */
  protected string $privateNote; 

  /**
   * ID setter
   */
  public function setId(int $id) {
    $this->id = $id;
    return $this;
  }

  /**
   * Transaction Date setter.
   */
  public function setTxnDate(string $txnDate) {
    $this->TxnDate = $txnDate;
    return $this;
  }
  /**
   * 
   * amount setter.
   */
  public function setAmount(string $amount) {
    $this->amount = $amount;
    return $this;
}
  /**
   * From Account number setter.
   */
  public function setFromAccountNo(string $fromAccountNo) {
      $this->fromAccountNo = $fromAccountNo;
      return $this;
  }

  /**
   * To Account number setter.
   */
  public function setToAccountNo(string $toAccountNo) {
    $this->toAccountNo = $toAccountNo;
    return $this;
}  

  /**
   * Private realmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
  }

  /**
   * Private note setter.
   */
  public function setPrivateNote(string $privateNote) {
    $this->privateNote = $privateNote;
    return $this;
  }

  /**
   * 'To' account number getter.
   */
  public function getToAccountNo() : int {
    return $this->toAccountNo;
  }
  /**
   * 'From' account number getter.
   */
  public function getFromAccountNo() : int {
    return $this->fromAccountNo;
  }
  /**
   * realmID getter.
   */
  public function getrealmId() : string {
      return $this->realmid;
  }

  /**
   * Transaction Date getter.
   */
  public function getTxnDate() : string {
      return $this->TxnDate;
  }
  /**
   * Private Note getter.
   */
  public function getPrivateNote() : string {
    return $this->privateNote;
  }
  /**
   * Amount getter.
   */
  public function getAmount() : float {
    return $this->amount;
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
   * Return details of the Transfer identified by $id
   * 
   * @return IPPIntuitEntity Returns an item of specified Id.
   * 
   */
  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->realmid);

      $dataService->forceJsonSerializers();
      $item = $dataService->FindbyId('Transfer', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
        throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
      }
      else {
          return $item;
      }
  }

  /**
   * Delete a Transfer from the QB system.
   *
   * @return bool 'true' if success.
   * 
   */
  public function delete(): bool{
    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    // Do not use $dataService->FindbyId to create the entity to delete
    // Use this simple representation instead
    // The problem is that FindbyId forces use of JSON and that doesnt work 
    // with the delete uri call
    $item = Transfer::create([
      "Id" => $this->id,
      "SyncToken" => "0"
    ]);
    
    $dataService->Delete($item);

    $error = $dataService->getLastError();
    if ($error) {
      throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    } else {      
      return true;
    }
  }  

  /**
   * Create this transfer in QBO
   * 
   * @return IPPIntuitEntity On success return an array with details of the new object. On failure return 'false'.
   */
  public function create() {

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    $transfer = Transfer::create([
      "TxnDate" => $this->TxnDate,
      "Amount" => strval($this->amount),
      "PrivateNote" => $this->privateNote,
      "FromAccountRef" => [
        "value" => strval($this->fromAccountNo)
      ],
      "ToAccountRef" => [
        "value" => strval($this->toAccountNo)
      ],
    ]);
    /** @var IPPIntuitEntity $result */
    $result = $dataService->Add($transfer);
    $error = $dataService->getLastError();
    if ($error) {
      throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    } else {      
      return $result;
    }
  }
}