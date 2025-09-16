<?php

namespace Controllers;

use Models\EncryptedXlsx;
use Models\PayrollCsv;
use Models\PayrollXlsx;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to acomplish Excel spreadsheet related tasks
 *
 * @category  Controller
*/
class XlsxCtl{

  /**
   * Delete all sporeadsheets from the upload directory, then receive an uploaded spreadsheet
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function upload(){  
    try {
        $uploaddir = XlsxCtl::getUploadDirectory();

        // Clean upload directory
        XlsxCtl::delete_all_spreadsheets($uploaddir); 
        XlsxCtl::delete_all_CSVs($uploaddir); 

        // Check that a file has been uploaded
        if (!$_FILES || !array_key_exists('file', $_FILES) || !$_FILES['file']) {
            throw new Exception("Uploaded file collection is empty or the uploaded ".
                "object is missing the key named 'file'.");
        }

        // Set the new file name
        $filename = XlsxCtl::getUploadedFilename(isset($_GET['filename'])?$_GET['filename']:'');
        $uploadpathandfile = $uploaddir . $filename;

        // Move file from PHP temp folder to upload directory
        if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadpathandfile))
        {
            echo json_encode(
                array("message" => "The file has been uploaded successfully.",
                "isEncrypted" =>   EncryptedXlsx::getInstance()
                                    ->isEncrypted($uploadpathandfile),
                "filename" => $filename)
            );
        } else {
            throw new Exception("There was an error uploading the file.");
        }
    } catch (Exception $e) {
        Error::response("Unable to clear upload directory and upload the new payroll file.", $e);
    }
  }

  /**
   * Extract all relevent data from the Payroll spreadsheet and then delete it.
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function parse(){  
    try {
        $uploaddir = XlsxCtl::getUploadDirectory();

        $decryptedFilePath = $uploaddir . 
            XlsxCtl::getDecryptedFilename(isset($_GET['filename'])?$_GET['filename']:'');

        if (!is_file($decryptedFilePath)) {
            throw new Exception('File not found. File name: ('. $decryptedFilePath .')');
        }

        // test if file is CSV or XLSX
        if (XlsxCtl::is_csv($decryptedFilePath)) {
            $model = PayrollCsv::getInstance()
                ->setFilePath($decryptedFilePath); 
        } else {
            $model = PayrollXlsx::getInstance()
                ->setFilePath($decryptedFilePath); 
        }

        if($model->parse(isset($_GET['payrolldate'])?$_GET['payrolldate']:'')) {
            if( !isset($_GET['keep_decrypted_file']) ) {
                // delete decrypted file
                if(is_file($decryptedFilePath)) {
                    unlink($decryptedFilePath);
                }
            }
        
            echo json_encode($model->getPayslips());

        } else {
            throw new Exception("Unable to parse spreadsheet for unknown reason.");
        }
            
    } catch (Exception $e) {
        Error::response("Error parsing payroll spreadsheet.", $e);
    }
  }

    /**
   * 
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function parse_worksheets(){  
    try {
        $uploaddir = XlsxCtl::getUploadDirectory();

        $decryptedFilePath = $uploaddir . 
        XlsxCtl::getDecryptedFilename(isset($_GET['filename'])?$_GET['filename']:'');


        if (!is_file($decryptedFilePath)) {
            throw new Exception('Decrypted file not found. File name: ('. $decryptedFilePath .')');
        }

        // test if file is CSV or XLSX
        if (XlsxCtl::is_csv($decryptedFilePath)) {
            $model = PayrollCsv::getInstance()
                ->setFilePath($decryptedFilePath); 
        } else {
            $model = PayrollXlsx::getInstance()
                ->setFilePath($decryptedFilePath); 
        }

        echo json_encode($model->parse_worksheets(), JSON_NUMERIC_CHECK);
        
    }
    catch (Exception $e){
        Error::response("Unable to parse worksheets.", $e);
    }
  }

  /**
   * Decrypt a spreadsheet using the data provided in the POST body.
   * 
   * @return void Output is echo'd directly to response 
   * 
   */
  public static function decrypt(){  
    try {
        $uploaddir = XlsxCtl::getUploadDirectory();

        $encryptedFilePath = $uploaddir . 
            XlsxCtl::getUploadedFilename(isset($_GET['filename'])?$_GET['filename']:'');

        $decryptedFilePath = $uploaddir . \Core\Config::read('file.decryptedfilename');

        $data = json_decode(file_get_contents("php://input"));
        if(!$data->password) {
            http_response_code(400);   
            echo json_encode(
                array("message" => "Password not provided.")
            );
            exit(1);
        }

        $model = EncryptedXlsx::getInstance()
            ->setEncryptedFilePath($encryptedFilePath)
            ->setPassword($data->password)
            ->setDecryptedFilePath($decryptedFilePath);

        $model->decrypt();       
        
        // delete encrypted file
        if(is_file($encryptedFilePath)) {
            unlink($encryptedFilePath);
        }

        if (is_file($decryptedFilePath)) {
            http_response_code(200);   
            echo json_encode(
                array("message" => "Spreadsheet decrypted.")
            );
        } else {
            throw new Exception("File not found. Decryption of spreadsheet failed.");
        }
    }
    catch (Exception $e){
        Error::response("Decryption of spreadsheet failed.", $e);
    }
  }

  
  /**
   * Helper function to get directory path of location to save uploaded files
   * @return string The directory path
   */
  private static function getUploadDirectory() {
    return \Core\Config::read('file.uploaddir') ?? "./uploads/";
  }
  
  /**
   * Helper function to get the file name of the uploaded file
   * @param string $filename 
   * @return string
   */
  private static function getUploadedFilename(string $filename = '') {
    // Set the new file name
    if(!empty($filename) ) {
        return $filename;
    } else {
        return \Core\Config::read('file.encryptedfilename');
    }
  }

/**
   * Helper function to get the file name of the decrypted file
   * @param string $filename 
   * @return string
   */
  private static function getDecryptedFilename(string $filename = '') {
    // Set the new file name
    if(!empty($filename) ) {
        return $filename;
    } else {
        return \Core\Config::read('file.decryptedfilename');
    }
  }

  /**
   * Helper function to delete all files that end with 'xlsx' in the specified directory
   * @param string $directory_name The directory to search for files to delete
   * @return void
   */
  private static function delete_all_spreadsheets(string $directory_name) {
    $files = glob($directory_name.'*xlsx'); // get xlsx file names in upload dir
    foreach($files as $file){ // iterate files
        if(is_file($file)) {
            unlink($file); // delete each spreadsheet
        }
    }
}

/**
   * Helper function to delete all files that end with 'csv' in the specified directory
   * @param string $directory_name The directory to search for files to delete
   * @return void
   */
  private static function delete_all_CSVs(string $directory_name) {
    $files = glob($directory_name.'*csv'); // get csv file names in upload dir
    foreach($files as $file){ // iterate files
        if(is_file($file)) {
            unlink($file); // delete each file
        }
    }
  }
   
    /**
     * 
     */
    private static function is_csv(string $filename):bool {

        $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 
            'application/octet-stream', 'application/vnd.ms-excel', 
            'application/x-csv', 'text/x-csv', 'text/csv', 
            'application/csv', 'application/excel', 
            'application/vnd.msexcel', 'text/plain'
        );

        $returnvalue = false;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filename);
        in_array($mime, $csvMimes) === true ? $returnvalue = true : $returnvalue = false;
        finfo_close($finfo);

        return $returnvalue;
    }
}