<?php

namespace Controllers;


class QBReportCtl{

  public static function profit_and_loss(){  

    $model = new \Models\QuickbooksReport();

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

}