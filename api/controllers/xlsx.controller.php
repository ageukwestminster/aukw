<?php

namespace Controllers;

use Models\EncryptedXlsx;

/**
 * Controller to acomplish Excel spreadsheet related tasks
 *
 * @category  Controller
*/
class XlsxCtl{


  /**
   * Return details of the User identified by $id
   *
   * @param int $id
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function decrypt(){  

    $data = json_decode(file_get_contents("php://input"));

    if(!$data || !$data->encryptedFilePath || !file_exists($data->encryptedFilePath)) {
        http_response_code(400);   
        echo json_encode(
            array("message" => "Encrypted file not found. Path: '" . 
                empty($data->encryptedFilePath)?'<empty>':$data->encryptedFilePath . "'")
        );
        exit(1);
    }

    if(!$data->decryptedFilePath) {
        http_response_code(400);   
        echo json_encode(
            array("message" => "Decrypted file path not provided.")
        );
        exit(1);
    }

    if(!$data->password) {
        http_response_code(400);   
        echo json_encode(
            array("message" => "Password not provided.")
        );
        exit(1);
    }

    $model = EncryptedXlsx::getInstance()
        ->setEncryptedFilePath($data->encryptedFilePath)
        ->setPassword($data->password)
        ->setDecryptedFilePath($data->decryptedFilePath);

    $model->decrypt();        
  }

   

}