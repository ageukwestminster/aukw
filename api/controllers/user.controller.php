<?php

namespace Controllers;

/**
 * Controller to acomplish User related tasks
 *
 * @category  Controller
 * @uses      
 * @version   0.0.1
 * @since     2021-02-27
 * @author    Neil Carthy <neil.carthy42@gmail.com>
*/
class UserCtl{

  /**
   * Return details of all Users
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  

    $model = new \Models\User();

    echo json_encode($model->read(), JSON_NUMERIC_CHECK);
  }

  /**
   * Return details of the User identified by $id
   *
   * @param int $id
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function read_one($id){  

    $model = new \Models\User();
    $model->id = $id;

    $model->readOne();

    if (empty($model->username) ) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "No User found with id = " . $model->id)
      );
      exit(1);
    }

    $user = array(
        "id" => $model->id,
        "username" => $model->username,
        "firstname" => html_entity_decode($model->firstname ?? ''),
        "surname" => html_entity_decode($model->surname ?? ''),
        "shopid" => $model->shopid,
        "role" => $model->role,
        "suspended" => $model->suspended,
        "email" => $model->email,
        "title" => $model->title,
    );

    echo json_encode($user, JSON_NUMERIC_CHECK);
  }

  /**
   * Add a new User to the database. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function create(){
    $model = new \Models\User();

    $data = json_decode(file_get_contents("php://input"));

    $model->username = $data->username;
    $model->role = $data->role;
    $model->suspended = $data->suspended;
    $model->firstname = $data->firstname;
    $model->surname = $data->surname;
    $model->shopid = $data->shopid;
    $model->email = $data->email;
    $model->title = $data->title;
    $model->failedloginattempts = isset($data->failedloginattempts)?$data->failedloginattempts:0;
    $model->password = password_hash($data->password, PASSWORD_DEFAULT);

    $model->checkPassword($data->password, $errors);
    if ($errors) {
        http_response_code(400);  
        echo json_encode(
          array("message" => implode(" & ",$errors))
        );
    } else if( $model->create()) {
      echo json_encode(
        array(
          "message" => "New user with id=$model->id was created.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
      // if unable to create the new_item, tell the admin
        http_response_code(400);  
        echo json_encode(
          array("message" => "Unable to INSERT row.")
        );
    }
  }

  /**
   * Update an existing User in the database with new data. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function update($id){
    $model = new \Models\User();

    $data = json_decode(file_get_contents("php://input"));

    $model->id = $id;
    $model->username = $data->username;
    $model->role = $data->role;
    $model->suspended = $data->suspended;
    $model->email = $data->email;
    $model->title = $data->title;
    $model->firstname = $data->firstname;
    $model->surname = $data->surname;
    $model->shopid = $data->shopid;
    if (isset($data->password) && !empty($data->password)) {
      $model->password = password_hash($data->password, PASSWORD_DEFAULT);
      $model->checkPassword($data->password, $errors);
      if ($errors) {
          http_response_code(400);  
          echo json_encode(
            array("message" => implode(" & ",$errors))
          );
          exit(1);
      }       
      $model->failedloginattempts = 0;
    } else {
        $model->failedloginattempts = isset($data->failedloginattempts)?$data->failedloginattempts:0;
    }


    if( $model->update()) {
      echo json_encode(
        array(
          "message" => "User with id=$model->id was updated.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to UPDATE row.",
            "id" => $model->id
          )
          , JSON_NUMERIC_CHECK);
    }
  }

  /**
   * Delete the user from the database that matches the given $id.
   *
   * @param int $id
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function delete($id){
    $model = new \Models\User();

    $model->id = $id;

    if( $model->delete()) {
      echo json_encode(
        array(
          "message" => "User with id=$model->id was deleted.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);
    } else{
        http_response_code(400);  
        echo json_encode(
          array(
            "message" => "Unable to DELETE row.",
            "id" => $model->id
          )
          , JSON_NUMERIC_CHECK);
    }
  }
}