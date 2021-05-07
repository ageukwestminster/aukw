<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\JournalEntry;

use DateTime;

class QuickbooksJournal{


  private $zero_rated_taxcode = [
    "value" => 4    
  ];
  private $zero_rated_taxrate = [
    "value" => 7    
  ];
  private $hmrc_entity = [
    "value" => 33,
    "name" => "HMRC VAT"
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
  private $sales_account = [
    "value" => 94,
    "name" => "Sales-Zero Rated"
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
  private $vat_liability_account = [
    "value" => 153,
    "name" => "VAT:VAT Liability"
  ];

  public $id;
  public $date;
  public $donations;
  public $cashDiscrepency;
  public $creditCards;
  public $cash;
  public $staffExpenses;
  public $volunteerExpenses;
  public $sales;
  public $cashToCharity;
  public $shopid;  

  public function readOne(){

      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare();
      $dataService->forceJsonSerializers();
      $journalentry = $dataService->FindbyId('journalentry', $this->id);
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

/*
  public function create_from_takings($takingsid){

    $model = new \Models\Takings();
    $model->id = $takingsid;
    $model->readone();



    $journal = array(
      "TxnDate" => $this->date,
      "DocNumber" => ""
    );
  }*/

  public function create(){

    $docnumber = (new DateTime($this->date))->format('Ymd') . 'H'; //'H' is short for Harrow Road

    $journal = array(
      "TxnDate" => $this->date,
      "DocNumber" => $docnumber,
      "Line" => [],
      "TxnTaxDetail"=> [
        "TaxLine" => [
          "Amount" => 0,
          "DetailType" => "TaxLineDetail",
          "TaxLineDetail" => [
            "TaxRateRef" => $this->zero_rated_taxrate,
            "PercentBased" => true,
            "TaxPercent" => 0,
            "NetAmountTaxable" => -round(abs($this->sales),2)
          ]
        ]
      ]
    );

    // This code will only add the respective line if amount != 0
    $this->journal_line($journal['Line'], "Cash Donations to Parent Charity", 
      $this->donations, $this->donations_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Overage / Underage", 
      $this->cashDiscrepency, $this->cash_discrepencies_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Total of customer payments by credit card.",
      $this->creditCards, $this->credit_card_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Total of customer payments in cash.",
      $this->cash, $this->undeposited_funds_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Cash that will be used as petty cash by Charity",
      $this->cashToCharity, $this->cash_to_charity_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Minor operating expenses paid in cash.",
      $this->staffExpenses, $this->other_expenses_account, $this->harrow_road_class);
    $this->journal_line($journal['Line'], "Volunteer expenses paid in cash.",
      $this->volunteerExpenses, $this->volunteer_expenses_account, $this->harrow_road_class);

    array_push($journal['Line'], [
      "Description" => "Zero-Rated Sales - Charity Shop Sales - Zero Rated",
      "Amount" => round(abs($this->sales),2),
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => $this->sales<0?"Debit":"Credit",
        "Entity" => [
          "Type" => "Vendor",
          "EntityRef" => $this->hmrc_entity
        ],
        "AccountRef" => $this->sales_account,
        "ClassRef" => $this->harrow_road_class,
        "TaxCodeRef" => $this->zero_rated_taxcode,
        "TaxApplicableOn" => "Sales",
        "TaxAmount" => 0
      ]
    ]);

    $theResourceObj = JournalEntry::create($journal);
    
    $auth = new QuickbooksAuth();
    $dataService = $auth->prepare();
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

  private function journal_line(&$line_array, $description, $amount, $account, $class) {
    if (abs($amount) <= 0.005) return;

    array_push($line_array, array(
      "Description" => $description,
      "Amount" => round(abs($amount),2),
      "DetailType" => "JournalEntryLineDetail",
      "JournalEntryLineDetail" => [
        "PostingType" => $amount<=0?"Debit":"Credit",
        "AccountRef" => $account,
        "ClassRef" => $class
      ]
    ));
  }
}