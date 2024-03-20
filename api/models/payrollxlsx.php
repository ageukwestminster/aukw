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
   * The WorkSheet object for the EE Summary sheet
   *
   * @var object
   */
  protected object $summaryWorkSheet;

  /**
   * The WorkSheet object for the Pensions sheet
   *
   * @var object
   */
  protected object $pensionsWorkSheet;
 
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
    if (!isset($this->pensionsWorkSheet) || !isset($this->summaryWorkSheet)) {
      $this->parse_worksheets();
    }
    return false;
  }

  /**
   * Open the spreadsheet file specified in the FilePath property and
   * store references to the pensions and summary worksheets.
   * @return object list of worksheet namesc
   */
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
        $this->pensionsWorkSheet = $spreadsheet->getSheetByName($name);
        $worksheets['pensions'] = $this->pensionsWorkSheet->getTitle();
      } else if (preg_match('/ee summary/i', $name)) {
        $this->summaryWorkSheet = $spreadsheet->getSheetByName($name);
        $worksheets['summary'] = $this->summaryWorkSheet->getTitle();
      }
    }

    return $worksheets;
  }


}