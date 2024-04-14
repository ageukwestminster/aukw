<?php

namespace Controllers;

/**
 * Controller to accomplish QBO General Journal related tasks. 
 *
 * @category  Controller
*/
class QBJournalCtl{

  /**
   * Return details of the QBO general journal identified by $id
   *
   * @param int $id
   * @return void Output is echo'd directly to response 
   */
  public static function read_one(int $id){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return an array of journals whose DocNumber starts with the given string.
   *
   * @param string $doc_number The string to match e.g. 'Payroll_2024_03'
   * @return void Output is echo'd directly to response 
   */
  public static function query_by_docnumber(string $doc_number){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksJournal();
    $model->realmid = $_GET['realmid'];

    echo json_encode($model->query_by_docnumber($doc_number), JSON_NUMERIC_CHECK);
  }

    /**
   * Delete from QBO the journal identified by $id
   *
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response 
   */
  public static function delete(int $id){  
    
    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksJournal();
    $model->id = $id;
    $model->realmid = $_GET['realmid'];

    if($model->delete()) {
      echo json_encode(
        array(
          "message" => "Journal entry with id=$id was deleted.",
          "id" => $id)
          , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to DELETE QB journal.",
            "id" => $id)
            , JSON_NUMERIC_CHECK);
    }
  }
}