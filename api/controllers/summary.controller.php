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

}