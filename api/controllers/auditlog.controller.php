<?php

namespace Controllers;

use DateTime;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish Audit Log related tasks. Audit logs record user actions in the system.
 * Audit log entries can be read and new entries can be created. Delete and update are not supported.
 *
 * @category  Controller
*/
class AuditLogCtl{

  /**
   * Return details of all Audit Log entries, with filtering via parameters
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read(): void {  
    try {
      $model = new \Models\AuditLog();

      $startDate = '';
      $endDate = '';

      // if parameters are provided use them
      if (isset($_GET['start']) || isset($_GET['end'])) {
        list($startDate, $endDate) = \Core\DatesHelper::sanitizeDateValues(
                                    $_GET['start'] ?? '', 
                                    $_GET['end'] ?? ''
                                );
      } 
      
      // default values are today and 3 months ago
      if ($startDate === '') {    
        if ($endDate === '') {           
          $endDate = date('Y-m-d');      
        }
        $startDate = (new DateTime($endDate))->modify('-3 month')->format('Y-m-d');
      } else if ($endDate === '') {           
        $endDate = (new DateTime($startDate))->modify('+3 month')->format('Y-m-d');
      } 

      $userId = isset($_GET['userid']) && is_numeric($_GET['userid']) ? (int)$_GET['userid'] : null;
      $eventType = isset($_GET['eventtype']) ? $_GET['eventtype'] : null;
      echo json_encode($model->read($userId, $startDate, $endDate, $eventType), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to return details of all Audit Log entries.", $e);
    }
  }

  /**
   * Add a new audit log entry to the database. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function create(): void {
    try {
      $model = new \Models\AuditLog();
      $data = json_decode(file_get_contents("php://input"));

      // Either userid or username must be supplied. If username is supplied then use the
      // user model object to find the id of that user.
      if (isset($data->userid)) {
        $model->userid = $data->userid;
      } else if (isset($data->username)) {
        // Using user model to find user id
        $user = new \Models\User();
        $user->username = $data->username;
        $user->readOneByUsername();
        if (empty($user->id)) {
          throw new Exception("No User found with username=$user->username.");
        }
        $model->userid = $user->id;
      } else {
        // Either userid or username must be supplied.
        throw new Exception("No user details provided. Provide one of userid or username.");
      }

      // Transfer property values
      $model->eventtype = $data->eventtype;
      $model->description = $data->description ?? '';
      if (isset($data->objecttype)) $model->objecttype = $data->objecttype;
      if (isset($data->objectid)) $model->objectid = $data->objectid;

      // Add to audit log
      if ($model->create()) {
        echo json_encode(
          [
            "message" => "New audit log entry with id=$model->id was created.",
            "id" => $model->id
          ],
          JSON_NUMERIC_CHECK
        );
      }
    } catch (Exception $e) {
      Error::response("Unable to insert entry into AuditLog.", $e);
    }
  }


  public static function read_eventtypes(): void {
    try {
      $model = new \Models\AuditLog();
      echo json_encode($model->read_eventtypes(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Unable to list event types in AuditLog.", $e);    
    }
  }
}