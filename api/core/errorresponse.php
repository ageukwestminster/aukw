<?php

namespace Core;

use Throwable;

/**
 * A static class to provide methods to handle exceptions
 * 
 * @category Core
 */
abstract class ErrorResponse { 
  /**
   * Send back a http error response and message.
   * @param Throwable $e Optional error object. Throwable used in case an exception inherits from Error.
   * @param string $message An optional message to include in the error response. Defaults to 'Error occurred.'.
   * @param int $errorCode The HTTP error code. Defaults to 400. Do not use 200.
   * @param string $extra Some extra information to include in the error response
   * @return never 
   */
  public static function response ( 
        string $message = 'Error occurred.', 
        ?Throwable $e = null,
        int $errorCode = 400, 
        string $extra = '')
  {
    http_response_code($errorCode);

    $output = array("message" => $message, 
                  "details" => $e?$e->getMessage():'');

    if ($extra != '') {
      $output['extra'] = $extra;
    }

    echo json_encode($output);

    exit(1);
  }
}