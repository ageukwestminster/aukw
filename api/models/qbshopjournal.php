<?php

namespace Models;

use Core\QuickbooksConstants as QBO;
use QuickBooksOnline\API\Facades\JournalEntry;

/**
 * Factory class that all creation of QB Employer NI journals
 * 
 * @category Model
 */
class QuickbooksEnterprisesJournal extends QuickbooksJournal{

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
     * Create the general journal entry for the shop
     */
    public function create_enterprises_journal($entries) {

      $payrolljournal = array(
          "TxnDate" => $this->TxnDate,
          "DocNumber" => $this->DocNumber,
          "Line" => [],
          "TotalAmt" => 0
      );

      foreach ($entries as $line) {
        //&$line_array, $description, $amount, $employee, $class, $account)
        $this->payrolljournal_line($payrolljournal['Line'], "Salary", 
            $line->totalPay, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::SALARIES_ACCOUNT);
        $this->payrolljournal_line($payrolljournal['Line'], "Salary", 
            -$line->totalPay, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::AUKW_INTERCO_ACCOUNT);
        
        if ($line->employerNI) {
          $this->payrolljournal_line($payrolljournal['Line'], "NI", 
              $line->employerNI, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::NI_ACCOUNT);
          $this->payrolljournal_line($payrolljournal['Line'], "NI", 
              -$line->employerNI, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::AUKW_INTERCO_ACCOUNT);
        }

        if ($line->employerPension) {
          $this->payrolljournal_line($payrolljournal['Line'], "Pension", 
              $line->employerPension, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::PENSIONS_ACCOUNT);
          $this->payrolljournal_line($payrolljournal['Line'], "Pension", 
              -$line->employerPension, $line->quickbooksId, QBO::HARROW_ROAD_CLASS, QBO::AUKW_INTERCO_ACCOUNT);
        }
      }
    
      $theResourceObj = JournalEntry::create($payrolljournal);
  
      $auth = new QuickbooksAuth();
      $dataService = $auth->prepare($this->getrealmId());
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
            "date" => $this->TxnDate,
            "label" => $this->DocNumber
        );
      }        
  }



}