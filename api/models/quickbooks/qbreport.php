<?php

namespace Models;

use QuickBooksOnline\API\ReportService\ReportService;
use QuickBooksOnline\API\Exception\SdkException;

/**
 * This is the parent class of a series of classes tha can be used to run selected QBO reports.
 * This class is abstract and cannot be instantiated. Use one of the child classes.
 *
 * A QBO Report is a complex object of the form:
 *
 * {
 *   "Header": {...},      // Basic attributes of the report such as Start & end dates
 *   "Columns": {...},     // Names and types of columns
 *   "Rows": {...},        // One or more Row objects containing the report values
 * }
 * The exact format is given in Report.xsd in the quickbooks api source code.
 *
 * @category Model
 */
abstract class QuickbooksReport
{
    private \QuickBooksOnline\API\DataService\DataService $dataService;
    protected ReportService $reportService;
    protected $report;

    /**
     * The start date of the report period.
     *
     * @var string
     */
    public string $startdate;
    /**
     * The end date of the report period.
     *
     * @var string
     */
    public string $enddate;
    /**
     * Predefined date range
     *
     * @var string
     */
    public string $dateMacro;
    /**
     * Group the P&L amounts by this column
     *
     * @var string
     */
    public string $summarizeColumn;
    /**
     * Only calculate item sales for this item. Use 'null' for all items.
     *
     * @var int|null
     */
    public int|null $item;
    /**
     * The QBO company ID
     *
     * @var string
     */
    public string $realmid;
    /**
     * A QBO account ID
     *
     * @var int
     */
    public int $account = 0;
    /**
     * A QBO column list
     *
     * @var string
     */
    public string $columns = '';
    /**
     * The QBO column to sort by. Usu 'tx_date'.
     *
     * @var string
     */
    public string $sortBy = '';
    /**
     * 'True' if should be sorted by date ascending
     *
     * @var bool
     */
    public bool $sortAscending = true;

    /**
     * Instantiate the QBO Dataservice, ServiceContext and ReportService.
     * The DataService and ReportService are retained as class variables
     * @return void
     */
    protected function prepare(): void
    {
        $auth = new QuickbooksAuth();
        $this->dataService = $auth->prepare($this->realmid);
        if ($this->dataService == false) {
            throw new SdkException("Unable to create DataService.");
        }
        $serviceContext = $auth->getServiceContext($this->realmid);
        if ($serviceContext == false) {
            throw new SdkException("Unable to create ServiceContext.");
        }
        $this->reportService = new ReportService($serviceContext);
        if ($this->reportService == false) {
            throw new SdkException("Unable to create ReportService.");
        }
    }

    /**Check to see if the most recent QBO request returned an error. If so,
     * then re-throw as a new SdkException.
     */
    protected function checkForError(): void
    {
        $error = $this->dataService->getLastError();
        if ($error) {
            throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
        }
    }

    /**
     * Run the QBO report
     */
    public function run()
    {
        $this->prepare();
        $report = $this->queryQuickBooks();
        $this->checkForError();
        return $report;
    }

    /**
     * Abstract function that, when implemented, performs the QBO query and returns a
     * QBO reponse object.
     */
    abstract protected function queryQuickBooks(): mixed;

    /**
     * Abstract function that, when implemented, converts the QBO report into a
     * simpler array object
     */
    abstract protected function adaptReport(): array;

}
