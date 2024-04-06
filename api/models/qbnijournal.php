<?php

namespace Models;

use Core\QuickbooksConstants as QBO;
use QuickBooksOnline\API\Facades\JournalEntry;

/**
 * Factory class that all creation of QB Employer NI journals
 * 
 * @category Model
 */
class QuickbooksEmployerNIJournal extends QuickbooksJournal{

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
     * Create the general journal entry for Employer NI 
     */
    public function create_employerNI_journal($entries) {

      $payrolljournal = array(
          "TxnDate" => $this->TxnDate,
          "DocNumber" => $this->DocNumber,
          "Line" => [],
          "TotalAmt" => 0
      );

      $sum = 0;
      foreach ($entries as $line) {
        //&$line_array, $description, $amount, $employee, $class, $account)
        // This code will only add the respective line if amount != 0
        $this->payrolljournal_line($payrolljournal['Line'], "", 
            $line->amount, $line->employeeId, $line->class, $line->account);
        
        $sum -= $line->amount;
      }

      $this->payrolljournal_line($payrolljournal['Line'], "", 
        $sum, '', QBO::ADMIN_CLASS, QBO::TAX_ACCOUNT);
    
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