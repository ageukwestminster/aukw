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
  public static function profit_and_loss(string $realmid){  

    $model = new \Models\QuickbooksReport();
    $model->realmid = $realmid;

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
   * Show a QBO general Ledger report.
   * HTTP parameters are: account, start, end, summarizeColumn
   *
   * @return void Output is echoed directly to response
   * 
   */
  public static function general_ledger(string $realmid){  

    $model = new \Models\QuickbooksReport();
    $model->realmid = $realmid;

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
        // must be 'Quarter', not 'quarter' or 'Month' not 'month'
        $model->summarizeColumn = ucwords(strtolower($_GET['summarizeColumn']));
    } else {
        $model->summarizeColumn = '';
    }

    if (isset($_GET['account']) && !empty($_GET['account'])) {
      $model->account = $_GET['account'];
    } 
    if (isset($_GET['sortAscending'])) {
      $model->sortAscending = true;
    } 
    if (isset($_GET['sortDescending'])) {
      $model->sortAscending = false;
    } 

    $model->columns = "tx_date,txn_type,doc_num,emp_name,memo," . 
              "split_acc,is_cleared,subt_nat_amount,rbal_nat_amount";
    $model->sortBy = "tx_date";

    try {
      echo json_encode($model->general_ledger(), JSON_NUMERIC_CHECK);
    }  catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to generate general ledger report. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
  }
  }

  /**
   * Show a QBO report that summarizes sales by a particuylar item.
   * HTTP parameters are: start, end, summarizeColumn, item
   *
   * @return void Output is echoed directly to response
   * 
   */
  public static function sales_by_item(string $realmid){  

    $model = new \Models\QuickbooksReport();
    $model->realmid = $realmid;

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
      // must be 'Quarter', not 'quarter' or 'Month' not 'month'
      $model->summarizeColumn = ucwords(strtolower($_GET['summarizeColumn']));
    } else {
      $model->summarizeColumn = '';
    }

    // item is a number
    if (isset($_GET['item']) && !empty($_GET['item'])) {
      $model->item = $_GET['item'];
    } else {
      $model->item = null;
    }

    echo json_encode($model->itemSales(), JSON_NUMERIC_CHECK);
  }

  public static function quarterly_market_report(string $realmid){

    $model = new \Models\QuickbooksReport();
    $model->realmid = $realmid;

    if (isset($_GET['summarizeColumn']) && !empty($_GET['summarizeColumn'])) {
      // must be 'Quarter', not 'quarter' or 'Month' not 'month'
      $model->summarizeColumn = ucwords(strtolower($_GET['summarizeColumn']));
    } else {
      $model->summarizeColumn = '';
    }

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

    list($start, $end) = \Core\DatesHelper::previousPeriod($start, $end );
    $model->startdate = $start;

    $profitAndLossReport = $model->profitAndLoss();
    $adaptedReport = $model->adaptProfitAndLossToQMA($profitAndLossReport);

    echo json_encode($adaptedReport, JSON_NUMERIC_CHECK);
  }

}