<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\SalesReceipt;

use DateTime;

class SalesReceiptJournal{


  private $zero_rated_taxcode = [
    "value" => 4    
  ];
  private $no_vat_taxcode = [
    "value" => 20    
  ];
  private $zero_rated_taxrate = [
    "value" => 7    
  ];
  private $harrow_road_class = [
    "value" => 400000000000618070,
    "name" => "Harrow Rd"
  ];
  private $other_expenses_account = [
    "value" => 8,
    "name"=> "Other Staff Expenses"
  ];
  private $volunteer_expenses_account = [
    "value" => 86,
    "name" => "Volunteer Expenses"
  ];
  private $cash_discrepencies_account = [
    "value" => 93,
    "name" => "Office Expense:Cash Discrepancies"
  ];
  private $ragging_discrepencies_account = [
    "value" => 93,
    "name" => "Office Expense:Cash Discrepancies"
  ];
  private $sales_account = [
    "value" => 191,
    "name" => "Daily Sales income"
  ];
  private $donations_account = [
    "value" => 81,
    "name" => "Donations to Parent"
  ];
  private $credit_card_account = [
    "value" => 96,
    "name" => "Credit Card Receipts"
  ];
  private $undeposited_funds_account = [
    "value" => 100,
    "name" => "Undeposited Funds"
  ];
  private $cash_to_charity_account = [
    "value" => 134,
    "name" => "Cash To Charity"
  ];
  private $ragging_account = [
    "value" => 82,
    "name" => "Ragging"
  ];
  private $clothing_item = [
    "value" => 37,
    "name" => "Daily Sales:Clothing"
  ];
  private $brica_item = [
    "value" => 38,
    "name" => "Daily Sales:Bric-a-Brac"
  ];
  private $books_item = [
    "value" => 39,
    "name" => "Daily Sales:Books"
  ];
  private $linens_item = [
    "value" => 40,
    "name" => "Daily Sales:Linens"
  ];
  private $cash_item = [
    "value" => 41,
    "name" => "Daily Sales:Cash"
  ];
  private $ccards_item = [
    "value" => 42,
    "name" => "Daily Sales:Credit Cards"
  ];
  private $overage_item = [
    "value" => 43,
    "name" => "Daily Sales:Overage/Underage"
  ];
  private $donations_item = [
    "value" => 44,
    "name" => "Daily Sales:Donations"
  ];
  private $ragging_item = [
    "value" => 45,
    "name" => "Daily Sales:Ragging"
  ];
  private $opexpenses_item = [
    "value" => 46,
    "name" => "Daily Sales:Operating Expenses"
  ];
  private $volexpenses_item = [
    "value" => 47,
    "name" => "Daily Sales:Volunteer Expenses"
  ];
  private $charitycash_item = [
    "value" => 48,
    "name" => "Daily Sales:Cash To Charity"
  ];

  private $customer = [
    "value" => 136,
    "name" => "Daily Sales"
  ];


  public $id;
  public $date;
  
  public $clothing;
  public $brica;
  public $books;
  public $linens;
  public $ragging;
  public $donations;

  public $volunteerExpenses;
  public $operatingExpenses;

  public $cash;
  public $creditCards;
  public $cashDiscrepency;
  public $cashToCharity;

  public $shopid; 
  public $privatenote;  

  public function readOne(){

      $auth = new QuickbooksAuth();
      try{
        $dataService = $auth->prepare();
      }
      catch (\Exception $e) {
        http_response_code(401);  
        echo json_encode(
          array("message" =>  $e->getMessage() )
        );
        return;
      }

      if ($dataService == false) {
        return;
      }

      $dataService->forceJsonSerializers();
      $journalentry = $dataService->FindbyId('salesreceipt', $this->id);
      $error = $dataService->getLastError();
      if ($error) {
          echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
          echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
          echo "The Response message is: " . $error->getResponseBody() . "\n";
      }
      else {
          return $journalentry;
      }
  }


  public function create(){

    $docnumber = (new DateTime($this->date))->format('Ymd') . 'H'; //'H' is short for Harrow Road

    $salesreceipt = array(
      "TxnDate" => $this->date,
      "DocNumber" => $docnumber,
      "PrivateNote" => $this->privatenote?$this->privatenote:"",
      "Line" => [],
      "TxnTaxDetail"=> [
        "TaxLine" => [
          "Amount" => 0,
          "DetailType" => "TaxLineDetail",
          "TaxLineDetail" => [
            "TaxRateRef" => $this->zero_rated_taxrate,
            "PercentBased" => true,
            "TaxPercent" => 0,
            "NetAmountTaxable" => round($this->sales,2)
          ]
        ]
          ],
      "CustomerRef" => $this->customer,
      "GlobalTaxCalculation" => "TaxExcluded",
      "TotalAmt" => abs($this->cash),
      "PrintStatus" => "NotSet",
      "EmailStatus" => "NotSet"
    );

    //&$line_array, $description, $amount, $item, $class, $quantity, $account, $taxcoderef)
    // This code will only add the respective line if amount != 0
    $this->salesreceipt_line($salesreceipt['Line'], "Daily sales of second-hand and donated clothing", 
      $this->clothing->sales, $this->clothing_item, $this->harrow_road_class,
      $this->clothing->number, $this->sales_account, $this->zero_rated_taxcode);
    $this->salesreceipt_line($salesreceipt['Line'], "Sales of donated household goods", 
      $this->brica->sales, $this->brica_item, $this->harrow_road_class,
      $this->brica->number, $this->sales_account, $this->zero_rated_taxcode);
    $this->salesreceipt_line($salesreceipt['Line'], "Sales of donated books and DVDs", 
      $this->books->sales, $this->books_item, $this->harrow_road_class,
      $this->books->number, $this->sales_account, $this->zero_rated_taxcode);
    $this->salesreceipt_line($salesreceipt['Line'], "Sales of donated linen products", 
      $this->linens->sales, $this->linens_item, $this->harrow_road_class,
      $this->linens->number, $this->sales_account, $this->zero_rated_taxcode);
    $this->salesreceipt_line($salesreceipt['Line'], "Cash donations to parent charity", 
      $this->donations->sales, $this->donations_item, $this->harrow_road_class,
      $this->donations->number, $this->donations_account, $this->no_vat_taxcode);      
    $this->salesreceipt_line($salesreceipt['Line'], "Textile/book recycling", 
      $this->ragging->sales, $this->ragging_item, $this->harrow_road_class,
      $this->ragging->number, $this->ragging_account, $this->zero_rated_taxcode);    

    $this->salesreceipt_line($salesreceipt['Line'], "Volunteer expenses paid in cash", 
      $this->volunteerExpenses, $this->volexpenses_item, $this->harrow_road_class,
      1, $this->volunteer_expenses_account, $this->no_vat_taxcode); //$quantity = 1
    $this->salesreceipt_line($salesreceipt['Line'], "Minor operating expenses paid in cash", 
      $this->operatingExpenses, $this->opexpenses_item, $this->harrow_road_class,
      1, $this->other_expenses_account, $this->no_vat_taxcode); //$quantity = 1

    $this->salesreceipt_line($salesreceipt['Line'], "Credit card payments received from customers", 
      $this->creditCards, $this->ccards_item, $this->harrow_road_class,
      1, $this->credit_card_account, $this->no_vat_taxcode); //$quantity = 1

    $this->salesreceipt_line($salesreceipt['Line'], "Cash discrepancies between sales total and cash/credit card subtotals", 
      $this->cashDiscrepency, $this->overage_item, $this->harrow_road_class,
      1, $this->cash_discrepencies_account, $this->no_vat_taxcode); //$quantity = 1

    $this->salesreceipt_line($salesreceipt['Line'], "Cash that has gone to the parent charity without being deposited into the Enterprises bank account", 
      $this->cashToCharity, $this->charitycash_item, $this->harrow_road_class,
      1, $this->cash_to_charity_account, $this->no_vat_taxcode); //$quantity = 1

    $theResourceObj = SalesReceipt::create($salesreceipt);
    
    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare();
    if ($dataService == false) {
      return false;
    }
    $resultingObj = $dataService->Add($theResourceObj);

    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
        return false;
    } else {      
      return array(
          "id" => $resultingObj->Id,
          "date" => $resultingObj->TxnDate,
          "label" => $resultingObj->DocNumber
      );
    }
  }

  private function salesreceipt_line(&$line_array, $description, $amount, $item, $class, $quantity, $account, $taxcoderef) {
    if (abs($amount) <= 0.005) return;

    array_push($line_array, array(
      "Description" => $description,
      "Amount" => $amount,
      "DetailType" => "SalesItemLineDetail",
      "SalesItemLineDetail" => [
        "ItemRef" => $item,
        "ClassRef" => $class,
        "UnitPrice" => $quantity==1?$amount:$amount/$quantity,
        "Qty" => $quantity,
        "ItemAccountRef" => $account,
        "TaxCodeRef" => $taxcoderef
      ]
    ));
  }
}