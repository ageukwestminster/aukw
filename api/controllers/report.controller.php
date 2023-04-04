<?php

namespace Controllers;

use DateTime;

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
      $model->startdate = (new DateTime())->modify('-18 month')->format('Y-m-d');
    }
    $model->shopID = 1;

    echo json_encode($model->dailySalesMovingAverage(), JSON_NUMERIC_CHECK);
  }

  
}