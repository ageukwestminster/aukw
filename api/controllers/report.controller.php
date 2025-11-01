<?php

namespace Controllers;

use DateTime;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to execute various reports on sales values and statistics.
 *
 * @category  Controller
*/
class ReportCtl{
 
  /**
   * Retrieve a data set for the histogram of daily sales chart.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function dailySalesHistogram(){  
    try {
      $model = new \Models\Report();

      if (isset($_GET['start']) || isset($_GET['end'])) {

        list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
            $_GET['start'] ?? '',
            $_GET['end'] ?? ''
        );
      
          $model->startdate = $start;
          $model->enddate = $end;
      } else {
          $model->startdate = '2000-01-01';
          $model->enddate = date('Y-m-d');
      }

      $model->shopID = $_GET['shopID'] ?? 1; // '1' is the shopID for Harrow Road.

      echo json_encode($model->dailySalesHistogram(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the histogram of daily sales chart.", $e);
    }
  }

  /**
   * Retrieve a data set for the chart showing daily sales moving averages.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function dailySalesMovingAverage(){  
    try {
      $model = new \Models\Report();

      $model->startdate = isset($_GET['start']) && \Core\DatesHelper::validateDate($_GET['start'])
        ? $_GET['start']
        : '2000-01-01';

      $model->shopID = $_GET['shopID'] ?? 1; // '1' is the shopID for Harrow Road.

      echo json_encode($model->dailySalesMovingAverage(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the chart showing daily sales moving averages.", $e);
    }
  }

    /**
   * Retrieve a data set for the report which shows average weekly sales, by quarter.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgWeeklySalesByQuarter($shopid){  
    try {
      $model = new \Models\TakingsSummary();

      echo json_encode($model->avgWeeklySales($shopid), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the report which shows average weekly sales, by quarter.", $e);
    }
  }

      /**
   * Retrieve a data set for the report which shows average weekly sales, within the given date range.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgWeeklySales($shopid){  
    try {
      $model = new \Models\Report();
      $model->shopID = $shopid;
      ReportCtl::GetHttpDateParameters($model);

      echo json_encode($model->avgWeeklySales(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the report which shows average weekly sales.", $e);
    }
  }

  /**
   * Retrieve a data set for the report which shows average daily sales per transaction, by quarter.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgDailyTransactionSizeByQuarter($shopid){  
    try {
      $model = new \Models\TakingsSummary();

      echo json_encode($model->avgDailyTransactionSizeByQuarter($shopid), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the report which shows ".
                              "average daily sales per transaction, by quarter.", $e);
    }
  }

    /**
   * Retrieve a data set for the report which shows average daily sales per transaction.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgDailyTransactionSize($shopid){  
    try {
      $model = new \Models\Report();
      $model->shopID = $shopid;
      ReportCtl::GetHttpDateParameters($model);

      echo json_encode($model->avgDailyTransactionSize(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to retrieve a dataset for the report which shows ".
                              "average daily sales per transaction.", $e);
    }
  }

  /**
   * Retrieve a data set for the table of daily/weekly/monthly sales that appears on the 
   * app home page.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function performanceSummary(){  
    try {
      $model = new \Models\TakingsSummary();

      echo json_encode($model->performanceSummary(NULL, NULL), JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
    } catch (Exception $e) {
      Error::response("Error retrieving performance summary.", $e);
    }
  }

  /**
   * Retrieve a data set for the chart showing monthly gross sales.
   * 
   * Used on home page of app.
   *
   * @return void Output is echoed directly to response.
   * 
   */  
  public static function salesChart(){  
    try {
      $model = new \Models\TakingsSummary();

      echo json_encode($model->salesChart(1, NULL), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving sales chart data.", $e);
    }
  }

  /**
   * Retrieve a data set for the chart showing the relative proportions of department sales.
   * 
   * Used on home page of app.
   *
   * @return void Output is echoed directly to response.
   * 
   */  
  public static function departmentChart(){  
    try {
      $model = new \Models\TakingsSummary();

      echo json_encode($model->departmentChart(NULL, NULL), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving department pie chart data.", $e);
    }
  }

  /**
   * A report of sales by month, listing total sales and average daily sales.
   * The average daily sales are broken down by department (clothing, brica, books, linens).
   * 
   * If no date info provided then it defaults to 1st Jan 2021.
   *
   * @param mixed $shopid Must be supplied.
   * @param null $year Used to specify when the tabulations begin.
   * @param null $month Used to specify when the tabulations begin.
   * @param null $day Used to specify when the tabulations begin.
   * 
   * @return void Output is echoed directly to response.
   * 
   */
  public static function salesByMonth($shopid, $year = null, $month = null, $day = null): void 
  {  
    try {
      $model = new \Models\TakingsSummary();
      $date = $year ? ($month ? ($day ? "$year-$month-$day" : "$year-$month-01") : "$year-01-01") : '2021-01-01';
      echo json_encode($model->salesByMonth($shopid, $date), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving sales by month data.", $e);
    }
  }

  /**
   * A report of sales by quarter, listing total sales and average daily sales
   * The average daily sales are broken down by department (clothing, brica, books, linens)
   * 
   * If no year info provided then it defaults to 1st Jan 2021
   *
   * @param mixed $shopid The id of the shop. Must be supplied.
   * @param null $year Can be used to specify when the tabulations begin.
   * 
   * @return void Output is echoed directly to response.
   * 
   */
  public static function salesByQuarter($shopid, $year  = null){  
    try {
      $model = new \Models\TakingsSummary();
      $date = $year ? "$year-01-01" : '2021-01-01';

      echo json_encode($model->salesByQuarter($shopid, $date), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving sales by quarter data.", $e);
    }
  }

    /**
   * A report of sales broken down by department (clothing, brica, books, linens)
   *
   * @param mixed $shopid The id of the shop. Must be supplied.
   * 
   * @return void Output is echoed directly to response.
   * 
   */
  public static function salesByDepartment(int $shopid){  
    try {
      $model = new \Models\Report();
      $model->shopID = $shopid;
      ReportCtl::GetHttpDateParameters($model);

      echo json_encode($model->salesByDepartment(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving sales by department data.", $e);
    }
  }

  /**
   * Show summarised takings data for a given shop, between start and end dates.
   * 
   * The values for a sample day would look like:
   *   {
   *     "id": 3934,
   *     "date": "2023-02-28",
   *     "shopid": 1,
   *     "shopname": "Harrow Road",
   *     "number_of_items_sold": 84,
   *     "customers_num_total": 44,
   *     "sales_total": 327.1,
   *     "rag": 0,
   *     "sales_total_inc_rag": 327.1,
   *     "expenses": 1.6,
   *     "cash_difference": -0.35,
   *     "total_after_expenses": 325.5,
   *     "daily_net_sales": 325.5,
   *     "comments": "",
   *     "quickbooks": 1
   *   }
   *
   * @param int $shopid The id of the shop. Must be supplied.
   * 
   * @return void Output is echoed directly to response 
   * 
   */
  public static function takingsSummary(int $shopid):void{  
    try {
      $model = new \Models\Takings();

      $startdate='';
      $enddate='';

      // if parameters are provided use them
      if (isset($_GET['start']) || isset($_GET['end'])) {
        list($startdate, $enddate) = \Core\DatesHelper::sanitizeDateValues(
            $_GET['start'] ?? '',
            $_GET['end'] ?? ''
        );
      } else {
          // default values are:
          // enddate: the most recent trading day, or today if none found
          // startdate: 3 months ago before end date
          $most_recent_takings = $model->read_most_recent($shopid);
          $enddate = $most_recent_takings['date'] ?? date('Y-m-d');
          $startdate = (new DateTime($enddate))->modify('-3 month')->format('Y-m-d');
      }  

      echo json_encode($model->summary($shopid, $startdate, $enddate), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving summarized takings data.", $e);
    }
  }

  /**
   * Show takings data for the last 'datapoints' days for a given shop
   *
   * @param mixed $shopid The id of the shop. Must be supplied.
   * @param mixed $numdatapoints 
   * 
   * @return void Output is echoed directly to response 
   * 
   */
  public static function salesList($shopid, $numdatapoints){  

    //$model = new \Models\Takings();

    //echo json_encode($model->salesList($shopid, $numdatapoints), JSON_NUMERIC_CHECK);

    throw new \Exception('Not implemented');
  }

    /**
   * A helper function to get Date parameters from the request. It looks for 
   * 'date_macro' first and then 'start' & 'end'. The values obtained are
   * set via the properties of the $model object.
   * @param Report $model A QBReport object
   * @return void Nothing is output or returned
   */
  private static function GetHttpDateParameters(\Models\Report $model) : void {

    if (isset($_GET['start']) || isset($_GET['end'])) {
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
          $_GET['start'] ?? '',
          $_GET['end'] ?? ''
      );
  
      $model->startdate = $start;
      $model->enddate = $end;
    }
    else {
      throw new Exception("Start/end dates are missing.");
    }
  }

    /**
   * Retrieve a data set for the chart showing the ratio of cash/creditcard moving averages.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function cashRatioMovingAverage(int $shopid){  
    try {
      $model = new \Models\Report();

      $model->startdate = isset($_GET['start']) && \Core\DatesHelper::validateDate($_GET['start'])
      ? $_GET['start']
      : '2018-01-01';

      $model->shopID = $shopid;

      echo json_encode($model->cashRatioMovingAverage(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving cash:CC ratio data.", $e);
    }
  }

  /**
   * Retrieve a data set for the chart showing the moving averages of sales
   * split by customer and by department
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function salesByDepartmentAndCustomerMovingAverage(int $shopid){  
    try {
      $model = new \Models\Report();

      $model->startdate = isset($_GET['start']) && \Core\DatesHelper::validateDate($_GET['start'])
      ? $_GET['start']
      : '2017-03-01'; //  customers_num_total field (in takings table) is only populated from this date.

      $model->shopID = $shopid;

      echo json_encode($model->salesByDepartmentAndCustomerMovingAverage(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving moving average data.", $e);
    }
  }
  
}