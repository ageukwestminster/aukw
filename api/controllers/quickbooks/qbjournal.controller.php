<?php

namespace Controllers\QuickBooks;

use Models\QuickbooksJournal;
use Models\QuickbooksQuery;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish QBO General Journal related tasks.
 *
 * @category  Controller
*/
class QBJournalCtl
{
    /**
     * Return details of the QBO general journal identified by $id
     * @param string $realmid The company ID for the QBO company.
     * @param int $id
     * @return void Output is echo'd directly to response
     */
    public static function read_one(string $realmid, int $id)
    {

        $model = QuickbooksJournal::getInstance()
          ->setRealmID($realmid)
          ->setId($id);

        echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
    }

    /**
     * Return an array of journals whose DocNumber starts with the given string.
     * @param string $realmid The company ID for the QBO company.
     * @param string $doc_number The string to match e.g. 'Payroll_2024_03'
     * @return void Output is echo'd directly to response
     */
    public static function query_by_docnumber(string $realmid, string $doc_number)
    {

        $journals = QuickbooksQuery::getInstance()
          ->setRealmID($realmid)
          ->query_by_docnumber('JournalEntry', $doc_number);

        echo json_encode($journals);
    }

    /**
   * Delete from QBO the journal identified by $id
   * @param string $realmid The company ID for the QBO company.
   * @param int $id The QBO id, not the DocNumber
   * @return void Output is echoed directly to response
   */
    public static function delete(string $realmid, int $id)
    {
        try {
            $model = QuickbooksJournal::getInstance()
              ->setRealmID($realmid)
              ->setId($id);

            if ($model->delete()) {
                echo json_encode(
                    array(
                    "message" => "Journal entry with id=$id was deleted.",
                    "id" => $id),
                    JSON_NUMERIC_CHECK
                );
            }
        } catch (Exception $e) {
            Error::response("Unable to delete QBO Journal with id=$id.", $e);
        }
    }
}
