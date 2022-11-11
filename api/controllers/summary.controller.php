<?php

namespace Controllers;

/**
 * Controller to summarise membership numbers
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2022-03-15
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class TakingsSummaryCtl{

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

}