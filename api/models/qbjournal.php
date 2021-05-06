<?php

namespace Models;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\JournalEntry;

class QuickbooksJournal{

    public $id;

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

    public function create(){

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare();

        $obj = [
            "TxnDate" => '2021-05-04',
            "DocNumber" => "20210504H",
            "Line" => [
            [
              "Id" => "0",
              "Description" => "Overage/Underage",
              "Amount" => 0.2,
              "DetailType" => "JournalEntryLineDetail",
              "JournalEntryLineDetail" => [
                "PostingType" => "Debit",
                "AccountRef" => [
                  "value" => 93,
                  "name" => "Office Expense:Cash Discrepancies"
                ],
                "ClassRef" => [
                  "value" => 400000000000618070,
                  "name" => "Harrow Rd"
                ]
             ]
            ],
            [
              "Id" => "1",
              "Description" => "Daily CC sales",
              "Amount" => 302.6,
              "DetailType" => "JournalEntryLineDetail",
              "JournalEntryLineDetail" => [
                "PostingType" => "Debit",
                  "AccountRef" => [
                    "value" => 96,
                    "name" => "Credit Card Receipts"
                  ],
                  "ClassRef" => [
                    "value" => 400000000000618070,
                    "name" => "Harrow Rd"
                  ]
              ]
            ],
            [
              "Id" => "2",
              "Description" => "Daily cash sales",
              "Amount" => 226,
              "DetailType" => "JournalEntryLineDetail",
              "JournalEntryLineDetail" => [
                "PostingType" => "Debit",
                  "AccountRef" => [
                    "value" => 100,
                    "name" => "Undeposited Funds"
                  ],
                  "ClassRef" => [
                    "value" => 400000000000618070,
                    "name" => "Harrow Rd"
                  ]
              ]
            ],
            [
              "Id" => "3",
              "Description" => "Zero-Rated Sales - Charity Shop Sales - Zero Rated",
              "Amount" => 528.8,
              "DetailType" => "JournalEntryLineDetail",
              "JournalEntryLineDetail" => [
                "PostingType" => "Credit",
                "Entity" => [
                  "Type" => "Vendor",
                  "EntityRef" => [
                    "value" => 33,
                    "name" => "HMRC VAT"
                  ]
                ],
                  "AccountRef" => [
                    "value" => 94,
                    "name" => "Sales-Zero Rated"
                  ],
                  "ClassRef" => [
                    "value" => 400000000000618070,
                    "name" => "Harrow Rd"
                  ],
                  "TaxCodeRef" => [
                    "value" => 4
                  ],
                  "TaxApplicableOn" => "Sales",
                  "TaxAmount" => 0
              ]
            ]
          ],
          "TxnTaxDetail"=> [
            "TaxLine" => [
                    "Amount" => 0,
                    "DetailType" => "TaxLineDetail",
                    "TaxLineDetail" => [
                        "TaxRateRef" => [
                            "value" => 7
                        ],
                        "PercentBased" => true,
                        "TaxPercent" => 0,
                        "NetAmountTaxable" => -528.8
                    ]
            ]
          ]
                      ];
        $theResourceObj = JournalEntry::create($obj);
      
        $resultingObj = $dataService->Add($theResourceObj);
        $error = $dataService->getLastError();
        if ($error) {
            echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
            echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
            echo "The Response message is: " . $error->getResponseBody() . "\n";
            return false;
        } else {      
            //echo json_encode(   $resultingObj );   
            return array(
                "id" => $resultingObj->Id,
                "date" => $resultingObj->TxnDate,
                "label" => $resultingObj->DocNumber
            );
        }
    }

    private function cash_discrepency_line($id, $amount, $class) {
      $account = [
        "value" => 93,
        "name" => "Office Expense:Cash Discrepancies"
      ];
      return journal_line($id, "Overage/Underage",$amount, $account, $class);
    }

    private function journal_line($id, $description, $amount, $account, $class) {
      return array(
        "Description" => $description,
        "Amount" => $amount,
        "DetailType" => "JournalEntryLineDetail",
        "JournalEntryLineDetail" => [
          "PostingType" => $amount<=0?"Debit":"Credit",
          "AccountRef" => $account,
          "ClassRef" => $class
       ]
      );
    }
}