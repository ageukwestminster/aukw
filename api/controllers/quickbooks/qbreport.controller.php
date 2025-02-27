<?php

namespace Controllers;

use DateTime;
use \Models\QbDateMacro;
use \Models\RowItem;

/**
 * Controller to accomplish QBO report related tasks.
 * 
 * The QBO API can only run reports that are in a pre-defined set. The available
 * reports are found in \\QuickBooksOnline\API\ReportService\ReportName.php
 *
 * @category Controller
*/
class QBReportCtl{

  /**
   * Show a QBO P&L report, exactly as it comes back from QBO. It will be 
   * in raw JSON format. It only shows data from the supplied range, not
   * the previous period.
   * 
   * HTTP parameters are: date_macro, start, end. Either date_macro OR 
   * both of start and end must be supplied
   * Note: SortBy,SortAscending, SummarizeColumn are not supported by this report
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   * 
   */
  public static function profit_and_loss_raw(string $realmid) : void { 
    echo json_encode(QBReportCtl::profit_and_loss_raw_impl($realmid), JSON_NUMERIC_CHECK);
  }

  /**
   * Show a P&L report for the date range supplied, and for the period 
   * 12 months before that range.
   * HTTP parameters are: date_macro, start, end. Either date_macro OR 
   * both of start and end must be supplied
   * Note: SortBy,SortAscending, SummarizeColumn are not supported by this report
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   */
  public static function profit_and_loss(string $realmid) : void {
    echo json_encode(QBReportCtl::profit_and_loss_impl($realmid), JSON_NUMERIC_CHECK);
  }

  /**
   * Show a P&L report for the date range supplied, and for the period 
   * 12 months before that range.
   * HTTP parameters are: date_macro, start, end. Either date_macro OR 
   * both of start and end must be supplied
   * Note: SortBy,SortAscending, SummarizeColumn are not supported by this report
   * @param string $realmid The id of the QBO company.
   * @return array The required report
   */
  private static function profit_and_loss_impl(string $realmid) : array {

    $model = new \Models\QBProfitAndLossReport();
    QBReportCtl::GetHttpDateParameters($model);

    try {

      QBReportCtl::profit_and_loss_raw_impl($realmid, $model);
      /* simplfy the P&L report received from QBO */
      $summariseCurrentPeriod = $model->adaptReport();
      
      $start = $summariseCurrentPeriod['start'];
      $end = $summariseCurrentPeriod['end'];

      // Do Previous year's values ... this means perform the P&L report again, this time
      // for a period that is 12 months before the current period
      $model->startdate = (new DateTime($start))->modify('-1 year')->format('Y-m-d');
      $model->enddate = (new DateTime($end))->modify('-1 year')->format('Y-m-d');
      QBReportCtl::profit_and_loss_raw_impl($realmid, $model); 
      $summarisePreviousPeriod = $model->adaptReport();
      
      return $model->mergecurrentAndPreviousPNLReports(
            $summariseCurrentPeriod, $summarisePreviousPeriod
        );

    } catch (\Throwable $e) {
      http_response_code(400);  
      echo json_encode(
        array(
          "message" => "Unable to generate p&l report.",
          "extra" => $e->getMessage()
        )
      );
      exit(1);
    }
  }

  
  /**
   * Show a QBO P&L report, exactly as it comes back from QBO. It will be 
   * in raw JSON format. It only shows data from the supplied range, not
   * the previous period.
   * 
   * HTTP parameters are: date_macro, start, end. Either date_macro OR 
   * both of start and end must be supplied
   * Note: SortBy,SortAscending, SummarizeColumn are not supported by this report
   * @param string $realmid The id of the QBO company.
   * @return mixed The required report
   * 
   */
  private static function profit_and_loss_raw_impl(string $realmid, $model = null) {  

    try {
      if (!$model) {
        $model = new \Models\QBProfitAndLossReport();        
        QBReportCtl::GetHttpDateParameters($model);
      }
      $model->realmid = $realmid;
      return $model->run();

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to generate p&l report. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
  }
}

  /**
   * A helper function to get Date parameters from the request. It looks for 
   * 'date_macro' first and then 'start' & 'end'. The values obtained are
   * set via the properties of the $model object.
   * @param QuickbooksReport $model A QBReport object
   * @return void Nothing is output or returned
   */
  private static function GetHttpDateParameters(\Models\QuickbooksReport $model) : void {

    if (isset($_GET['date_macro']) && !empty($_GET['date_macro'])) {
      try {
        //Check that its a valid date_macro value
        $date_macro =  QbDateMacro::from($_GET['date_macro']);
        $model->dateMacro = $date_macro->value;
      } catch (\Throwable $e) {
        http_response_code(400);  
        echo json_encode(
            array(
                "message" => "Unable to generate p&l report: invalid date_macro supplied.",
                "extra" => str_replace('"',"'", $e->getMessage())
                )
        );
        exit(1);
      }      
    }
    else if(isset($_GET['start']) || isset($_GET['end'])) {
      $start='';
      $end='';
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
  
      $model->startdate = $start;
      $model->enddate = $end;
    }
    else {
      http_response_code(400);  
      echo json_encode(array("message" => "Unable to generate p&l report, date_macro and start/end dates are missing."));
      exit(1);
    }
  }

    /**
   * Show a QBO general Ledger report.
   * HTTP parameters are: account, start, end, summarizeColumn
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   * 
   */
  public static function general_ledger(string $realmid){  

    $model = new \Models\QBGeneralLedgerReport();
    $model->realmid = $realmid;
    QBReportCtl::GetHttpDateParameters($model);

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
      $model->run();
      echo json_encode($model->adaptReport(), JSON_NUMERIC_CHECK);
    } catch (\Exception $e) {
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
   * Show a QBO report that summarizes sales by a particular item.
   * HTTP parameters are: start, end, summarizeColumn, item
   * @param string $realmid The id of the QBO company.
   * @param bool $raw If 'true' then return the QBO report without adapting it
   * @return void Output is echoed directly to response
   * 
   */
  public static function sales_by_item(string $realmid, bool $raw = false){  
    $model = new \Models\QBItemSalesReport();
    QBReportCtl::sales_by_item_impl($realmid, $model);
    echo json_encode($model->adaptReport(), JSON_NUMERIC_CHECK);
  }

  /**
   * Show a QBO report that summarizes sales by a particular item.
   * HTTP parameters are: start, end, summarizeColumn, item
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   * 
   */
  public static function sales_by_item_raw(string $realmid){  
    echo json_encode(QBReportCtl::sales_by_item_impl($realmid), JSON_NUMERIC_CHECK);
  }

    /**
   * Show a QBO report that summarizes sales by a particular item.
   * HTTP parameters are: start, end, summarizeColumn, item
   * @param string $realmid The id of the QBO company.
   * @return mixed
   * 
   */
  private static function sales_by_item_impl(string $realmid, $model = null){
    if (!$model) $model = new \Models\QBItemSalesReport();
    $model->realmid = $realmid;
    QBReportCtl::GetHttpDateParameters($model);

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

    return $model->run();
  }


  /**
   * Show a report that provides information to assist with completing the CRA
   * quarterly market analysis (QMA) report. This report is very similar to the
   * P&L report but with the added line items of ragging, donations and in-store
   * customer sales.
   * 
   * HTTP parameters are: date_macro, start, end. Either date_macro OR 
   * both of start and end must be supplied
   * Note: SortBy,SortAscending, SummarizeColumn are not supported by this report
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   * 
   */
  public static function quarterly_market_report(string $realmid){

    try {

      $pnlReport = QBReportCtl::profit_and_loss_impl($realmid);

      $pnlReport['title'] = "QMA Report";

      // Find ragging & in-store customer sales
      if ($pnlReport['income'] && property_exists($pnlReport['income'], 'rows')) {
        foreach ($pnlReport['income']->rows as $rowItem) {
          if ($rowItem->displayName == 'Ragging') {
            $pnlReport['ragging'] = $rowItem;
          } else if ($rowItem->displayName == 'Daily Sales Income' || 
              $rowItem->displayName == 'Sales-20% VAT' ||
              $rowItem->displayName == 'Sales-Zero Rated'
          ) {
            if (array_key_exists('instorecustomersales', $pnlReport)) {
              $pnlReport['instorecustomersales']->Add($rowItem);
            } else {
              $pnlReport['instorecustomersales'] = $rowItem;
            }
          }
        }
      }

      // Find donations
      if (array_key_exists('otherincome',$pnlReport) && 
                  property_exists($pnlReport['otherincome'], 'rows')) {
        foreach ($pnlReport['otherincome']->rows as $rowItem) {
          if ($rowItem->displayName == 'Donations to Parent') {
            $pnlReport['donations'] = $rowItem;
          }
        }
      }

      // Fill in any blanks
      if (!array_key_exists('ragging', $pnlReport)) {
        $rowItem = new RowItem;
        $rowItem->displayName = 'Ragging';
        $pnlReport['ragging'] = $rowItem;
      }
      if (!array_key_exists('instorecustomersales', $pnlReport)) {
        $rowItem = new RowItem;
        $rowItem->displayName = 'Daily Sales Income';
        $pnlReport['instorecustomersales'] = $rowItem;
      }
      if (!array_key_exists('donations', $pnlReport)) {
        $rowItem = new RowItem;
        $rowItem->displayName = 'Donations to Parent';
        $pnlReport['donations'] = $rowItem;
      }

      // Deduce miscellaneous income
      $rowItem = new RowItem;
      $rowItem->displayName = 'Miscellaneous Income';
      $rowItem->currentValue = round($pnlReport['income']->currentValue 
          -$pnlReport['instorecustomersales']->currentValue 
          -$pnlReport['ragging']->currentValue,2) ;
      $rowItem->previousValue = round($pnlReport['income']->previousValue 
          -$pnlReport['instorecustomersales']->previousValue 
          -$pnlReport['ragging']->previousValue,2) ;
      $pnlReport['miscellaneousincome'] = $rowItem;

      echo json_encode($pnlReport, JSON_NUMERIC_CHECK);

    } catch (\Exception $e) {
      http_response_code(400);  
      echo json_encode(
          array(
              "message" => "Unable to generate QMA report. ",
              "extra" => $e->getMessage()
              )
      );
      exit(1);
  }

  }

    /**
   * Show a QBO report that summarizes ragging by quarter
   * The only HTTP parameter accepted is 'start' and that is optional. 
   * If 'start' is not supplied then the query defaults to the quarter 
   * start date of 5 years ago.
   * @param string $realmid The id of the QBO company.
   * @return void Output is echoed directly to response
   * 
   */
  public static function ragging_by_quarter(string $realmid){  

    $model = new \Models\QBItemSalesReport();
    $model->realmid = $realmid;
    $model->summarizeColumn = 'Quarter';
    $model->item = null;
    $model->sortAscending = false;

    // From https://stackoverflow.com/a/35509890/6941165
    $current_quarter = ceil(date('n') / 3);
    $first_date_of_current_quarter = 
        date('Y-m-d', strtotime(date('Y') . '-' . (($current_quarter * 3) - 2) . '-1'));
    // Next line not used but kept for reference
    //$last_date_of_current_quarter = date('Y-m-t', strtotime(date('Y') . '-' . (($current_quarter * 3)) . '-1'));
    $end = (new DateTime($first_date_of_current_quarter))->modify('-1 day')->format('Y-m-d');
    

    if(isset($_GET['start'])) {
      $start=$_GET['start'];
    } else {
      // if no start date provided then go back 5 years
      $start=(new DateTime($first_date_of_current_quarter))->modify('-5 year')->format('Y-m-d');
    }

    list($start, $end) = \Core\DatesHelper::sanitizeDateValues($start, $end);
  
    $model->startdate = $start;
    $model->enddate = $end;

    $model->run();

    echo json_encode($model->extractRaggingNumbers(), JSON_NUMERIC_CHECK);
  }

}