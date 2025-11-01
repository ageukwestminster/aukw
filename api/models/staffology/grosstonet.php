<?php
namespace Models\Staffology;

use DateTime;
use Exception;

use Core\Config;
use Models\Payslip;
use Services\PayrollApiService;
/**
 * Model to retrieve Gross-To-Net report from Staffology Payroll API
 * 
 * @category Model
 */
class GrossToNetReport{
  /**
  * Service object to call Payroll API
  * @var PayrollApiService|null
  */ 
  private $apiService;
  /**
   * The Staffology Employer Id
   *
   * @var string
   */
  protected string $employerId;
    /**
   * The Staffology tax year. It's a string like 'Year2025', 'Year2024', etc.
   *
   * @var string
   */
  protected string $taxYear;

  /**
   * The from period
   *
   * @var int
   */
  protected int $fromPeriod;
  /**
   * The to period
   *
   * @var int
   */
  protected int $toPeriod;
  /**
   * The sort order, done on Payroll Code (i.e. employee number)
   *
   * @var bool
   */
  protected bool $sortDescending;

    /**
   * sortDescending setter
   */
  public function setSortDescending(bool $sortDescending) {
    $this->sortDescending = $sortDescending;
    return $this;
  }

  /**
   * sortDescending getter.
   */
  public function getSortDescending() : bool {
    return $this->sortDescending;
  } 

  /**
   * fromPeriod setter
   */
  public function setFromPeriod(int $fromPeriod) {
    $this->fromPeriod = $fromPeriod;
    return $this;
  }

  /**
   * fromPeriod getter.
   */
  public function getFromPeriod() : int {
    return $this->fromPeriod;
  } 

  /**
   * toPeriod setter
   */
  public function setToPeriod(int $toPeriod) {
    $this->toPeriod = $toPeriod;
    return $this;
  }

  /**
   * toPeriod getter.
   */
  public function getToPeriod() : int {
    return $this->toPeriod;
  } 

  /**
   * Employer Id setter
   */
  public function setEmployerId(string $employerId) {
    $this->employerId = $employerId;
    return $this;
  }

  /**
   * Employer ID getter.
   */
  public function getEmployerId() : string {
    return $this->employerId;
  } 

   /**
   * Employer Id setter
   */
  public function setTaxYear(string $taxYear) {
    $this->taxYear = $taxYear;
    return $this;
  }

  /**
   * Employer ID getter.
   */
  public function getTaxYear() : string {
    return $this->taxYear;
  }  
  /**
   * Constructor
   */
  protected function __construct(){
    $this->apiService = new PayrollApiService();
  }

  /**
   * Static constructor / factory
   */
  public static function getInstance() {    
    return new self();
  }

  /**
   * Go to the external payroll api and download the Gross-To-Net report for the given year and month.
   */
  public function generate(){

    // Build endpoint
    $endpoint = 'employers/' . $this->employerId 
                  . '/reports/'
                  . $this->taxYear
                  . '/'
                  . Config::read('staffology.payperiod') 
                  . '/gross-to-net';

    $params = array(
      'fromPeriod' => $this->fromPeriod,
      'toPeriod' => $this->toPeriod,
      'sortDescending' => $this->sortDescending ? 'true' : 'false'
    );

    $report = $this->apiService->get($endpoint, $params);
    
    $salaryData=array();

    if (isset($report['model']) &&isset($report['model']['lines']) && is_array($report['model']['lines'])) {
      foreach ($report['model']['lines'] as $payslip) {
        $salaryData[] = $payslip;        
      }
    } else {
      throw new \Exception("Unexpected response from Staffology API when retrieving GrossToNet. Not an Array.");
    }
    
    return $salaryData;
    
  }
        
}