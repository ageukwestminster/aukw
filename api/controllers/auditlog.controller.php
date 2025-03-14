<?php

namespace Controllers;

use DateTime;

/**
 * Controller to acomplish Audit Log related tasks
 *
 * @category  Controller
*/
class AuditLogCtl{

  /**
   * Return details of all Audit Log entries, with filtering via parameters
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read(){  

    $model = new \Models\AuditLog();

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

    if (isset($_GET['userid']) && !empty($_GET['userid']) && is_numeric($_GET['userid'])) {
      echo json_encode($model->read((int)$_GET['userid'], $startdate, $enddate), JSON_NUMERIC_CHECK);
    } else {
      echo json_encode($model->read(null, $startdate, $enddate), JSON_NUMERIC_CHECK);
  }

  }

  /**
   * Add a new audit log entry to the database. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function create(){

    $model = new \Models\AuditLog();
    $data = json_decode(file_get_contents("php://input"));

    // Either userid or username must be supplied. If username is supplied then use the
    // user model object to find the id of that user.
    if (isset($data->userid)) {
      $model->userid = $data->userid;
    }
    else if(isset($data->username)){
      // Using user model to find user id
      $user = new \Models\User();
      $user->username = $data->username;
      $user->readOneByUsername();
      if (empty($user->id) ) {
        // User not found
        http_response_code(400);   
        echo json_encode(
            array("message" => "No User found with username=$user->username.")
        );
        exit(1);
      }
      $model->userid = $user->id;
    }
    else {
      // Either userid or username must be supplied.
      http_response_code(400);   
      echo json_encode(
          array("message" => "No user details provided. Provide one of userid or username.")
      );
      exit(1);
    }

    // Transfer property values
    $model->eventtype = $data->eventtype;
    $model->description = isset($data->description)?$data->description:'';
    if (isset($data->objecttype)) $model->objecttype = $data->objecttype;
    if (isset($data->objectid)) $model->objectid = $data->objectid;

    // Add to audit log
    if( $model->create()) {
      echo json_encode(
        array(
          "message" => "New audit log entry with id=$model->id was created.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
      // if unable to insert the record, tell the admin
        http_response_code(400);  
        echo json_encode(
          array("message" => "Unable to INSERT row.")
        );
    }
  }

}