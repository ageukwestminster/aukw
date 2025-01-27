<?php

namespace Controllers;

use DateTime;

/**
 * Controller to accomplish Report related tasks. 
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

    $model = new \Models\Report();

    if(isset($_GET['start']) || isset($_GET['end'])) {
      $start='';
      $end='';
      list($start, $end) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
  
      $model->startdate = $start;
      $model->enddate = $end;
  } else {
      $model->startdate = '2000-01-01';
      $model->enddate = date('Y-m-d');
  }

  if (isset($_GET['shopID']) && !empty($_GET['shopID'])) {
      $model->shopID = $_GET['shopID'];
  } else {
      $model->shopID = 1;
  }

    echo json_encode($model->dailySalesHistogram(), JSON_NUMERIC_CHECK);
  }

  /**
   * Retrieve a data set for the chart showing daily sales moving averages.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function dailySalesMovingAverage(){  

    $model = new \Models\Report();

    if(isset($_GET['start']) && \Core\DatesHelper::validateDate($_GET['start'])) {
        $model->startdate = $_GET['start'];
    } else {
      $model->startdate = '2000-01-01';
    }
    if (isset($_GET['shopID']) && !empty($_GET['shopID'])) {
      $model->shopID = $_GET['shopID'];
    } else {
        $model->shopID = 1;
    }

    echo json_encode($model->dailySalesMovingAverage(), JSON_NUMERIC_CHECK);
  }

    /**
   * Retrieve a data set for the report which shows average weekly sales, by quarter.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgWeeklySales($shopid){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->avgWeeklySales($shopid), JSON_NUMERIC_CHECK);
  }

  /**
   * Retrieve a data set for the report which shows average daily sales per transaction, by quarter.
   *
   * @param mixed $shopid Must be supplied.
   * @return void Output is echoed directly to response.
   * 
   */
  public static function avgDailyTransactionSize($shopid){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->avgDailyTransactionSize($shopid), JSON_NUMERIC_CHECK);
  }

  /**
   * Retrieve a data set for the table of daily/weekly/monthly sales that appears on the 
   * app home page.
   *
   * @return void Output is echoed directly to response.
   * 
   */
  public static function performanceSummary(){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->performanceSummary(NULL, NULL), JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
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

    $model = new \Models\TakingsSummary();

    echo json_encode($model->salesChart(1, NULL), JSON_NUMERIC_CHECK);
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

    $model = new \Models\TakingsSummary();

    echo json_encode($model->departmentChart(NULL, NULL), JSON_NUMERIC_CHECK);
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
  public static function salesByMonth($shopid, $year = null, $month = null, $day = null){  

    $model = new \Models\TakingsSummary();
    $date = '';
    if (!$year) {
      $date = '2021-01-01';
    }
    else if (!$month) {
      $date = $year . '-01-01';
    }
    else if (!$day) {
      $date = $year . '-' . $month .'-01';
    }
    else {
      $date = $year . '-' . $month .'-' . $day;
    }
    echo json_encode($model->salesByMonth($shopid, $date), JSON_NUMERIC_CHECK);
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
  public static function salesByQuarter($shopid, $year = null){  

    $model = new \Models\TakingsSummary();
    $date = '';
    if (!$year) {
      $date = '2021-01-01';
    }
    else {
      $date = $year . '-01-01';
    }

    echo json_encode($model->salesByQuarter($shopid, $date), JSON_NUMERIC_CHECK);
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
  public static function takingsSummary(int $shopid){  

    $model = new \Models\Takings();

    $startdate='';
    $enddate='';

    // if parameters are provided use them
    if(isset($_GET['start']) || isset($_GET['end'])) {
      list($startdate, $enddate) = \Core\DatesHelper::sanitizeDateValues(
                                  !isset($_GET['start']) ? '' : $_GET['start'], 
                                  !isset($_GET['end']) ? '' : $_GET['end']
                              );
    } 
    
    // default values are:
    // enddate: the most recent trading day, or today if none found
    // startdate: 3 months ago before end date
    if ($startdate == '') {    
      if ($enddate == '') {           

        // Check for the most recent shop takings in the database
        //$most_recent_takings=array();
        $most_recent_takings=$model->read_most_recent($shopid);
        if ($most_recent_takings && $most_recent_takings['date']) {
          $enddate = $most_recent_takings['date'];
        }
        else {
          $enddate = date('Y-m-d');      
        }

      }
      
      $startdate = (new DateTime($enddate))->modify('-3 month')->format('Y-m-d');
    } else if ($enddate == '') {           
      $enddate = (new DateTime($startdate))->modify('+3 month')->format('Y-m-d');
    }    

    echo json_encode($model->summary($shopid, $startdate, $enddate), JSON_NUMERIC_CHECK);
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
  
}