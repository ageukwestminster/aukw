<?php

namespace Models;

use QuickBooksOnline\API\Facades\Purchase;
use QuickBooksOnline\API\Exception\SdkException;
use QuickBooksOnline\API\Data\IPPIntuitEntity;

/**
 * Factory class that provides data about QBO Purchases.
 * Note: A Purchase is also known as an Expense.
 * 
 * @category Model
 */
class QuickbooksPurchase{
 
  /** In QBO this can be one of 'Cash', 'Cheque' or 'CreditCard'. In this system
   * it is always 'Cash'.
   */
  const PAYMENTTYPE = "Cash";  
  /** In QBO this can be one of 'TaxExcluded', 'TaxIncluded' or 'NotApplicable'. In this system
   * it is always 'TaxIncluded'.
   */
  const TAXCALCULATIONTYPE = "TaxExcluded";  

  /**
   * The QBO id of the Quickbooks Purchase.
   *
   * @var string
   */
  protected string $id;

   /**
   * The QBO sync token of the Quickbooks Purchase.
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
  protected string $txnDate;  
  /**
   * The number of the entity that the expense is paid to.
   *
   * @var int
   */
  protected int $entity;  
  /**
   * The number of the bank account that the expense is paid from.
   *
   * @var int
   */
  protected int $bankAccount;  
  /**
   * The number of the account that the expense is accounted into.
   *
   * @var int
   */
  protected int $expenseAccount; 
  /**
   * A memo about the purchase
   *
   * @var string
   */
  protected string $privateNote; 
    /**
   * A label for the expense. Max 21 characters.
   *
   * @var string
   */
  protected string $docNumber;
  /**
   * A description of the expense.
   *
   * @var string
   */
  protected string $description; 
  /**
   * The amount of money that was paid as part of the purchase, must be positive and non-zero
   *
   * @var float
   */
  protected float $amount; 
  /**
   * The amount of tax that was paid as part of the purchase, must be positive but can be zero
   *
   * @var float
   */
  protected float $taxAmount; 
  /**
   * A reference to the tax code
   *
   * @var mixed
   */
  protected mixed $taxCode; 
    /**
   * The tax rate, usually either 0 or 20 (per cent)
   *
   * @var mixed
   */
  protected mixed $taxRate; 
  /**
   * ID setter
   */
  public function setId(int $id) {
    $this->id = $id;
    return $this;
  }

  /**
   * ID setter
   */
  public function setSyncToken(int $syncToken) {
    $this->syncToken = $syncToken;
    return $this;
  }
  /**
   * Transaction Date setter.
   */
  public function setTxnDate(string $txnDate) {
    $this->txnDate = $txnDate;
    return $this;
  }
  /**
   * Tax Code setter.
   */
  public function setTaxCode(array $taxCode) {
    $this->taxCode = $taxCode;
    return $this;
  }  
  /**
   * Transaction DocNumber setter.
   */
  public function setDocNumber(string $docNumber) {
    $this->docNumber = $docNumber;
    return $this;
  }
  /**
   * 
   * Bank Account number setter.
   */
  public function setBankAccount(int $bankAccount) {
      $this->bankAccount = $bankAccount;
      return $this;
  }
  /**
   * 
   * Expense Account number setter.
   */
  public function setExpenseAccount(int $expenseAccount) {
    $this->expenseAccount = $expenseAccount;
    return $this;
  }  
  /**
   * From Entity number setter.
   */
  public function setEntity(int $entity) {
    $this->entity = $entity;
    return $this;
  }
  /**
   * From Entity number setter.
   */
  public function setTaxRate(mixed $taxRate) {
    $this->taxRate = $taxRate;
    return $this;
  }
  /**
   * RealmID setter.
   */
  public function setRealmID(string $realmid) {
    $this->realmid = $realmid;
    return $this;
  }
  /**
   * Line description setter.
   */
  public function setDescription(string $description) {
    $this->description = $description;
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
   * Amount setter.
   */
  public function setAmount(float $amount) {
    $this->amount = $amount;
    return $this;
  }
  /**
   * Tax Amount setter.
   */
  public function setTaxAmount(float $taxAmount) {
    $this->taxAmount = $taxAmount;
    return $this;
  }

  /**
   * TaxAmount getter.
   */
  public function getTaxAmount() : float {
    return $this->taxAmount;
  }
  /**
   * Amount getter.
   */
  public function getamount() : float {
    return $this->amount;
  }
  /**
   * Bank account number getter.
   */
  public function getBankAccountNo() : int {
    return $this->bankAccount;
  }
  /**
   * Expense account number getter.
   */
  public function getExpenseAccountNo() : int {
    return $this->expenseAccount;
  }  
  /**
   * Description getter.
   */
  public function getDescription() : string {
    return $this->description;
  }
  /**
   * Doc number getter.
   */
  public function getDocNumber() : string {
    return $this->docNumber;
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
      return $this->txnDate;
  }
  /**
   * Private Note getter.
   */
  public function getPrivateNote() : string {
    return $this->privateNote;
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
      $item = $dataService->FindbyId('Purchase', $this->id);
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
    $item = Purchase::create([
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
   * Create this purchase in QBO
   * 
   * @return IPPIntuitEntity On success return an array with details of the new object. On failure return 'false'.
   */
  public function create() {

    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare($this->realmid);

    $purchase = Purchase::create([
      "TxnDate" => $this->txnDate,
      "PaymentType" => self::PAYMENTTYPE,
      "GlobalTaxCalculation" => self::TAXCALCULATIONTYPE,
      "EntityRef" => [
        "value" => strval($this->entity)
      ],
      "AccountRef" => [
        "value" => strval($this->bankAccount)
      ],
      "Line" => [
        "Description" => $this->description,
        "Amount" => $this->amount,
        "DetailType" => "AccountBasedExpenseLineDetail",
        "AccountBasedExpenseLineDetail" => [
          "AccountRef" => $this->expenseAccount,
          "TaxCodeRef" => ["value"=>$this->taxCode['value']], //4 for zero, 2 for 20%
        ]        
        ],
      "TxnTaxDetail"=> [
        "TaxLine" => [
          "Amount" => $this->taxAmount,
          "DetailType" => "TaxLineDetail",
          "TaxLineDetail" => [
            "TaxRateRef" => $this->taxRate, //value is 8 for zero, 4 for 20%
            "PercentBased" => true,
            "TaxPercent" => $this->taxCode['rate'],
            "NetAmountTaxable" => round($this->amount-$this->taxAmount,2)
          ]
        ]
      ],
      "DocNumber" => $this->docNumber,
      "PrivateNote" => $this->privateNote,
    ]);

    /** @var IPPIntuitEntity $result */
    $result = $dataService->Add($purchase);
    $error = $dataService->getLastError();
    if ($error) {
      throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
    } else {      
      return $result;
    }
  }
}