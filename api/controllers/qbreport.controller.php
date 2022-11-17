<?php

namespace Controllers;


class ReportCtl{

  public static function profit_and_loss(){  

    $model = new \Models\QuickbooksReport();
    /*$model->start = $start;
    $model->end = $end;
    $model->sortbycolumn = $sortbycolumn;*/

    $model->start = '2021-10-01';
    $model->end = '2022-09-30';

    echo json_encode($model->profitAndLoss(), JSON_NUMERIC_CHECK);
  }

}