<?php

namespace Core;

use Throwable;
use Exception;

/**
 * A static class to provide methods to handle exceptions
 * 
 * @category Core
 */
abstract class ErrorResponse { 
  /**
   * 
   * @param Throwable $e 
   * @param string $message 
   * @param int $errorCode 
   * @return never 
   */
  public static function response ( 
        string $message = 'Error ocurred.', 
        ?Throwable $e = null,
        int $errorCode = 400, 
        string $extra = '')
  {
    http_response_code($errorCode);

    $output = array("message" => $message, 
                  "details" => $e->getMessage());

    if ($extra != '') {
      $output['extra'] = $extra;
    }

    echo json_encode($output);

    exit(1);
  }
}