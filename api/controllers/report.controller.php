<?php

namespace Controllers;

use DateTime;

/**
 * Controller to accomplish Report related tasks. 
 *
 * @category  Controller
*/
class ReportCtl{

 
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

  public static function performanceSummary(){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->performanceSummary(NULL, NULL), JSON_NUMERIC_CHECK| JSON_UNESCAPED_SLASHES);
  }

  public static function salesChart(){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->salesChart(1, NULL), JSON_NUMERIC_CHECK);
  }

  public static function departmentChart(){  

    $model = new \Models\TakingsSummary();

    echo json_encode($model->departmentChart(NULL, NULL), JSON_NUMERIC_CHECK);
  }

  /*****
   * 
   * A report of sales by month, listing total sales and average daily sales
   * The average daily sales are broken down by department (clothing, brica, books, linens)
   * 
   * $shopid must be supplied
   * $year, $month, $day can be used to specify when the tabulations begin
   * If no date info provided then it defaults to 1st Jan 2021
   * 
   ***/
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

  /*****
   * 
   * A report of sales by quarter, listing total sales and average daily sales
   * The average daily sales are broken down by department (clothing, brica, books, linens)
   * 
   * $shopid must be supplied
   * $year can be used to specify when the tabulations begin
   * If no year info provided then it defaults to 1st Jan 2021
   * 
   ***/
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

  public static function takingsSummary($shopid){  

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
    
    // default values are today and 3 months ago
    if ($startdate == '') {    
      if ($enddate == '') {           
        $enddate = date('Y-m-d');      
      }
      $startdate = (new DateTime($enddate))->modify('-3 month')->format('Y-m-d');
    } else if ($enddate == '') {           
      $enddate = (new DateTime($startdate))->modify('+3 month')->format('Y-m-d');
    }    

    echo json_encode($model->summary($shopid, $startdate, $enddate), JSON_NUMERIC_CHECK);
  }

  public static function salesList($shopid, $numdatapoints){  

    $model = new \Models\Takings();

    echo json_encode($model->salesList($shopid, $numdatapoints), JSON_NUMERIC_CHECK);
  }
  
}