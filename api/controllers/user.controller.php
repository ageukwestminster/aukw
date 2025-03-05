<?php

namespace Controllers;

use \Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to acomplish User related tasks
 *
 * @category  Controller
*/
class UserCtl{

  /**
   * Return details of all Users
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all(){  
    try {
      $model = new \Models\User();

      echo json_encode($model->read(), JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of all Users.", $e);
    }
  }

  /**
   * Return details of the User identified by $id
   *
   * @param int $id
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function read_one(int $id){  
    try {
      $model = new \Models\User();
      $model->id = $id;

      $model->readOneByUserID();

      if (empty($model->username) ) {
        throw new Exception("No User found with id = $model->id");
      }

      // Done in this repetitive way to exclude 2 properties: password and failedloginattempts
      $user = array(
          "id" => $model->id,
          "username" => $model->username,
          "firstname" => $model->firstname,
          "surname" => $model->surname,
          "shopid" => $model->shopid,
          "role" => $model->role,
          "suspended" => $model->suspended,
          "email" => $model->email,
          "title" => $model->title,
          "quickbooksUserId" => $model->quickbooksUserId,
      );

      echo json_encode($user, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of User with id=$id.", $e);
    }
  }

    /**
   * Return details of the User identified by name and email address
   *
   * @param string $firstname The given name of the user
   * @param string $surname The family name of the user
   * @param string $email The email address of the user
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function read_one_by_name_and_email(){  
    try {
      $model = new \Models\User();

      if(!isset($_GET['firstname']) || !isset($_GET['surname']) || !isset($_GET['email'])) {
        throw new Exception("Please supply firstname, surname and email as http parameters.");
      } 

      $model->firstname = $_GET['firstname'];
      $model->surname = $_GET['surname'];
      $model->email = $_GET['email'];

      $model->readOneByNameAndEmail();

      if (empty($model->username) ) {
        throw new Exception("No User found with that name and email address.");
      }

      // Done in this repetitive way to exclude 2 properties: password and failedloginattempts
      $user = array(
          "id" => $model->id,
          "username" => $model->username,
          "firstname" => $model->firstname,
          "surname" => $model->surname,
          "shopid" => $model->shopid,
          "role" => $model->role,
          "suspended" => $model->suspended,
          "email" => $model->email,
          "title" => $model->title,
          "quickbooksUserId" => $model->quickbooksUserId,
      );

      echo json_encode($user, JSON_NUMERIC_CHECK);
    } catch (Exception $e) {
      Error::response("Error retrieving details of a User identified by name and email address.", $e);
    }
  }

  /**
   * Add a new User to the database. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function create(){
    try {
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
        throw new Exception( implode(" & ",$errors));
      } 
      
      $model->create();

      echo json_encode(
        array(
          "message" => "New user with id=$model->id was created.",
          "id" => $model->id
        )
      , JSON_NUMERIC_CHECK);

    } catch (Exception $e) {
      Error::response("Error inserting new User.", $e);
    }
  }

  /**
   * Update an existing User in the database with new data. Parameters are supplied via POST data.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function update($id){
    try {
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
          throw new Exception( implode(" & ",$errors));
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
        throw new Exception("Updating user with id=$id failed for unknown reason.");
      }
    } catch (Exception $e) {
      Error::response("Error updating User with id=$id.", $e);
    }
  }

  /**
   * Delete from the database the user that matches the given $id.
   *
   * @param int $id The id of the user to delete.
   * 
   * @return void Output is echo'd directly to response
   * 
   */
  public static function delete($id){
    try {
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
        throw new Exception("Deleting user with id=$id failed for unknown reason.");
      }
    } catch (Exception $e) {
      Error::response("Error deleting User with id=$id.", $e);
    }
  }
}