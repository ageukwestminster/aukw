<?php

namespace Controllers;

/**
 * Controller to accomplish QBO report related tasks.
 * 
 * The QBO API can only run reports that are in a pre-defined set. The available
 * reports are found in \\QuickBooksOnline\API\ReportService\ReportName.php
 *
 * @category  Controller
*/
class QBReportCtl{


  /**
   * Show a QBO P&L report.
   * HTTP parameters are: start, end, summarizeColumn
   *
   * @return void Output is echoed directly to response
   * 
   */
  public static function profit_and_loss(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksReport();
    $model->realmid = $_GET['realmid'];

    if(isset($_GET['start']) || isset($_GET['end'])) {
      $start='';
      $end='';
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
  
      $model->startdate = $start;
      $model->enddate = $end;
    }

    if (isset($_GET['summarizeColumn']) && !empty($_GET['summarizeColumn'])) {
        $model->summarizeColumn = $_GET['summarizeColumn'];
    } else {
        $model->summarizeColumn = '';
    }

    echo json_encode($model->profitAndLoss(), JSON_NUMERIC_CHECK);
  }

    /**
   * Show a QBO eneral Ledger report.
   * HTTP parameters are: start, end, summarizeColumn
   *
   * @return void Output is echoed directly to response
   * 
   */
  public static function general_ledger(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksReport();
    $model->realmid = $_GET['realmid'];

    if(isset($_GET['start']) || isset($_GET['end'])) {
      $start='';
      $end='';
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
  
      $model->startdate = $start;
      $model->enddate = $end;
    }

    if (isset($_GET['summarizeColumn']) && !empty($_GET['summarizeColumn'])) {
        $model->summarizeColumn = $_GET['summarizeColumn'];
    } else {
        $model->summarizeColumn = '';
    }

    echo json_encode($model->general_ledger(), JSON_NUMERIC_CHECK);
  }

  /**
   * Show a QBO report that summarizes sales by a particuylar item.
   * HTTP parameters are: start, end, summarizeColumn, item
   *
   * @return void Output is echoed directly to response
   * 
   */
  public static function sales_by_item(){  

    if(!isset($_GET['realmid']) ) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Please supply a value for the 'realmid' parameter.")
      );
      exit(1);
    } 

    $model = new \Models\QuickbooksReport();
    $model->realmid = $_GET['realmid'];

    if(isset($_GET['start']) || isset($_GET['end'])) {
      $start='';
      $end='';
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
  
      $model->startdate = $start;
      $model->enddate = $end;
    }

    if (isset($_GET['summarizeColumn']) && !empty($_GET['summarizeColumn'])) {
        $model->summarizeColumn = $_GET['summarizeColumn'];
    } else {
        $model->summarizeColumn = '';
    }

    if (isset($_GET['item']) && !empty($_GET['item'])) {
      $model->item = $_GET['item'];
    } else {
      $model->item = null;
    }

    echo json_encode($model->itemSales(), JSON_NUMERIC_CHECK);
  }

}