<?php

namespace Models;

use OLE;

/**
 * A class that can decrypt encrypted Xlsx files. 
 * 
 * A normal Xlsx file is in Office OpenXML format. An encrypted
 * file is actually an encrypted OPC zip package inside a
 * compound OLE document.
 * 
 * @category Model
 */
class EncryptedXlsx{

  /**
   * The file path to the encrypted XL spreadsheet,
   * including full file name.
   *
   * @var string
   */
  protected string $encryptedFilePath;
  /**
   * The password to open the encrypted file.
   *
   * @var string
   */
  protected string $password;
  /**
   * The file path to what will be the decrypted XL spreadsheet,
   * including full file name.
   *
   * @var string
   */
  protected string $decryptedFilePath;
 
  /**
   * Encrypted File Path setter
   */
  public function setEncryptedFilePath(string $encryptedFilePath) {
    $this->encryptedFilePath = $encryptedFilePath;
    return $this;
  }
  /**
   * Password setter
   */
  public function setPassword(string $password) {
    $this->password = $password;
    return $this;
  }
  /**
   * Decrypted File Path setter
   */
  public function setDecryptedFilePath(string $decryptedFilePath) {
    $this->decryptedFilePath = $decryptedFilePath;
    return $this;
  }
  


  /**
   * Constructor
   */
  protected function __construct(){}

  /**
   * Static constructor / factory
   */
  public static function getInstance() {
    return new self();
  }

    /**
   * Decrypt an encrypted Excel xlsx file and save as a decrypted file.
   * (Requires PEAR/OLE to open the file and extract the encryption data and payload.)
   * 
   * Code is from https://github.com/jaydadhania08/PHPDecryptXLSXWithPassword
   */
  public function isEncrypted(string $filename):bool {
    $oleObj = new OLE();

    if (!is_file($filename)) {
      throw new \Exception('File not found. File name: ('. $filename??'<empty>' .')');
    }

    try {
      $oleObj -> read($filename);
      
      // Look for the Encryption Info
      $xmlstr = substr($this->getDataByName($oleObj, 'EncryptionInfo'), 8);

      if ($xmlstr) {
        return true;
      } else {
        return false;
      }
    } catch (\Exception $e) {
      http_response_code(400);   
      echo json_encode(
          array("message" => "Unable to check spreadsheet for encryption.",
          "details" => $e->getMessage())
      );
      exit(1);
    }
  }

  /**
   * Decrypt an encrypted Excel xlsx file and save as a decrypted file.
   * (Requires PEAR/OLE to open the file and extract the encryption data and payload.)
   * 
   * Code is from https://github.com/jaydadhania08/PHPDecryptXLSXWithPassword
   */
  public function decrypt() {
    $oleObj = new OLE();
    $oleObj -> read($this->encryptedFilePath);
    
    // parse info from XML
    {
      $xmlstr = substr($this->getDataByName($oleObj, 'EncryptionInfo'), 8);

      if (!$xmlstr) {
        throw new \Exception('This file ('. $this->encryptedFilePath .') is not encrypted.');
      }

      $xml =  new \SimpleXMLElement($xmlstr);

      $info = [];

      $info['keyDataSalt'] = base64_decode((string) $xml -> keyData -> 
                                                    attributes() -> saltValue);

      $passwordAttributes = $xml -> xpath("//*[@spinCount]")[0] -> attributes();

      $info['passwordSalt'] = base64_decode((string) $passwordAttributes -> saltValue);
      $info['passwordHashAlgorithm'] = (string) $passwordAttributes -> hashAlgorithm;
      $info['encryptedKeyValue'] = base64_decode((string) 
                                $passwordAttributes -> encryptedKeyValue);
      $info['spinValue'] = (int) $passwordAttributes -> spinCount;
      $info['passwordKeyBits'] = (int) $passwordAttributes -> keyBits;
    }

    // get key
    {
      $h = hash($info['passwordHashAlgorithm'], $info['passwordSalt'] . iconv('UTF-8'
                , 'UTF-16LE', $this->password), true);

      for($i = 0; $i < $info['spinValue']; $i++)
      {
        $h = hash($info['passwordHashAlgorithm'], pack('I', $i) . $h, true);
      }

      $blockKey = hex2bin('146e0be7abacd0d6');

      $h_final = hash($info['passwordHashAlgorithm'], $h . $blockKey, true);

      $encryptionKey = substr($h_final, 0, intval($info['passwordKeyBits'] / 8));

      $mode = 'SHA512' === $info['passwordHashAlgorithm'] ? 'aes-256-cbc' : 'aes-128-cbc';

      $key = openssl_decrypt($info['encryptedKeyValue'], $mode, $encryptionKey
                  , OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $info['passwordSalt']);
    }

    // decrypt data
    {
      // get encrypted payload
      $payload = $this->getDataByName($oleObj, 'EncryptedPackage');

      // determine total size of decrypted data
      $totalSize = unpack('I', substr($payload, 0, 4))[1];

      // actual payload
      $payload = substr($payload, 8);

      $SEGMENT_LENGTH = 4096;

      $decrypted = '';

      for($i = 0; ; $i++)
      {
        $start = $i * $SEGMENT_LENGTH;
        $end = $start + $SEGMENT_LENGTH;

        $payloadChunk = substr($payload, $start, $SEGMENT_LENGTH);

        $saltWithBlockKey = $info['keyDataSalt'] . pack('I', $i);

        $iv = hash($info['passwordHashAlgorithm'], $saltWithBlockKey, true);

        $iv = substr($iv, 0, 16);

        $mode = 'SHA512' === $info['passwordHashAlgorithm'] ? 'aes-256-cbc' : 'aes-128-cbc';

        $decryptedChunk = openssl_decrypt($payloadChunk, $mode, $key
                          , OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);

        $decrypted .= $decryptedChunk;

        if($end >= strlen($payload))
        {
          break;
        }
      }

      $decrypted = substr($decrypted, 0, $totalSize);
    }

    // write to file
    file_put_contents($this->decryptedFilePath, $decrypted);

  }

  /**
   * Get a stream of data from an OLE file with the specified stream name. This is used
   * in the decrypt function to extract EncryptionInfo (details of the encryption
   * algorithim) and EncryptedPackage (the encrypted payload).
   */
  function getDataByName($oleObj, $name)
  {
    $objArray = array_filter($oleObj -> _list, function($obj) use ($name) {
      return $name === $obj -> Name;
    });
  
    if(0 === count($objArray))
    {
      return false;
    }
  
    return $oleObj -> getData(array_values($objArray)[0] -> No, 0, -1);
  }


}