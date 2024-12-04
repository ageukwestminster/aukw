<?php

namespace Controllers;

/**
 * Controller to acomplish Audit Log related tasks
 *
 * @category  Controller
*/
class AuditLogCtl{

  /**
   * Return details of all Audit Log entries
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    $model = new \Models\AuditLog();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
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