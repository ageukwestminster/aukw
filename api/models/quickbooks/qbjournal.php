<?php

namespace Models;

use Exception;
use QuickBooksOnline\API\Exception\IdsException;
use QuickBooksOnline\API\Facades\JournalEntry;
use QuickBooksOnline\API\Exception\SdkException;
use ReflectionException;

/**
 * Factory class that provides data about QBO General Journals.
 *
 * @category Model
 */
class QuickbooksJournal
{
    /**
     * The QBO id of the Quickbooks Journal.
     *
     * @var int
     */
    protected int $id;

    /**
     * The QBO company ID
     *
     * @var string
     */
    protected string $realmid;

    /**
     * The transaction date of the Journal entry
     *
     * @var string
     */
    protected string $TxnDate;

    /**
     * The Reference number for the transaction. Does not have to be unique.
     *
     * @var string
     */
    protected string $DocNumber;

    /**
     * ID setter
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Private realmID setter.
     */
    public function setRealmID(string $realmid)
    {
        $this->realmid = $realmid;
        return $this;
    }

    /**
     * ID getter.
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * realmID getter.
     */
    public function getrealmId(): string
    {
        return $this->realmid;
    }

    /**
     * Transaction Date setter.
     */
    public function setTxnDate(string $txnDate)
    {
        $this->TxnDate = $txnDate;
        return $this;
    }

    /**
     * Reference number setter.
     */
    public function setDocNumber(string $docNumber)
    {
        $this->DocNumber = $docNumber;
        return $this;
    }

    /**
     * Reference number getter.
     */
    public function getDocNumber(): string
    {
        return $this->DocNumber;
    }

    /**
     * Transaction Date getter.
     */
    public function getTxnDate(): string
    {
        return $this->TxnDate;
    }

    /**
     * Constructor
     */
    protected function __construct()
    {
    }

    /**
     * Static constructor / factory
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Return details of the QBO general journal identified by $id
     * @return object|null Returns an journal with specified Id or nothing.
     *
     */
    public function readOne(): object|null
    {

        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->realmid);

        $dataService->forceJsonSerializers();
        $journalentry = $dataService->FindbyId('journalentry', $this->id);
        $error = $dataService->getLastError();
        if ($error) {
            throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
        } else {
            if (property_exists($journalentry, 'JournalEntry')) {
                /** @disregard Intelephense error on next line */
                return $journalentry->JournalEntry;
            } else {
                return $journalentry;
            }
        }
    }

    /**
     * Delete this journal from the QBO system.
     * @return true 'true' if success.
     * @throws Exception
     * @throws SdkException
     * @throws ReflectionException
     * @throws IdsException
     */
    public function delete(): true
    {
        $auth = new QuickbooksAuth();
        $dataService = $auth->prepare($this->realmid);

        // Do not use $dataService->FindbyId to create the entity to delete
        // Use this simple representation instead
        // The problem is that FindbyId forces use of JSON and that doesnt work
        // with the delete uri call
        $journal = JournalEntry::create([
          "Id" => $this->id,
          "SyncToken" => "0"
        ]);

        $dataService->Delete($journal);

        $error = $dataService->getLastError();
        if ($error) {
            throw new SdkException("The QBO Response message is: " . $error->getResponseBody());
        } else {
            return true;
        }
    }

    /**
     * Push a new array describing a single line of a QBO journal into the given array
     * Helper function used in create.
     *
     * @param mixed $line_array The given array, passed by reference.
     * @param mixed $description
     * @param mixed $amount
     * @param mixed $employee
     * @param mixed $class
     * @param mixed $account
     *
     * @return void
     *
     */
    protected function payrolljournal_line(&$line_array, $description, $amount, $employee, $class, $account)
    {
        if (abs($amount) <= 0.005) {
            return;
        }

        array_push($line_array, array(
          "Description" => $description,
          "Amount" => abs($amount),
          "DetailType" => "JournalEntryLineDetail",
          "JournalEntryLineDetail" => [
            "PostingType" => ($amount < 0 ? "Credit" : "Debit"),
            "Entity" => [
                "Type" => "Employee",
                "EntityRef" => $employee
            ],
            "AccountRef" => $account,
            "ClassRef" => $class,
          ]
        ));
    }
}
