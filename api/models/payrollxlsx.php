<?php

namespace Models;

/**
 * A class that can decrypt encrypted Xlsx files. 
 * 
 * A normal Xlsx file is in Office OpenXML format. An encrypted
 * file is actually an encrypted OPC zip package inside a
 * compound OLE document.
 * 
 * @category Model
 */
class PayrollXlsx{

  /**
   * The file path to the payroll data spreadsheet,
   * including full file name.
   *
   * @var string
   */
  protected string $filePath;
 
  /**
   * Encrypted File Path setter
   */
  public function setFilePath(string $filePath) {
    $this->filePath = $filePath;
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


  public function parse(): bool {
    return false;
  }

  public function parse_worksheets() {

    /**  Create a new Reader of the type defined in $inputFileType  **/
    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
    /**  Advise the Reader that we only want to load cell data  **/
    $reader->setReadDataOnly(true);
    /**  Load $inputFileName to a Spreadsheet Object  **/
    $spreadsheet = $reader->load($this->filePath);

    $names  = $spreadsheet->getSheetNames();
    $worksheets = array();

    foreach ($names as $name) {
      if (preg_match('/pensions report/i', $name)) {
        $worksheets['pensions'] = $spreadsheet->getSheetByName($name);
      } else if (preg_match('/ee summary/i', $name)) {
        $worksheets['summary'] = $spreadsheet->getSheetByName($name);
      }
    }

    return $worksheets;
  }


}