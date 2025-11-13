<?php

namespace Controllers\QuickBooks;

use Models\QuickbooksQuery;
use Core\QuickbooksConstants as QBO;
use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish QBO Transfer related tasks.
 *
 * @category  Controller
*/
class QBTaxCtl
{
    /**
     * Return details of the QBO Transfer identified by $id
     *
     * @param string $realmid The company ID for the QBO company.
     * @return void Output is echo'd directly to response
     */
    public static function read_all(string $realmid)
    {
        try {
            $taxCodes = QuickbooksQuery::getInstance()
              ->setRealmID($realmid)
              ->list_tax_codes();

            echo json_encode($taxCodes, JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Unable to retrieve QB Tax Codes.", $e);
        }
    }

    /**
   * Return details of the QBO Transfer identified by $id
   *
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response
   */
    public static function read_one(string $realmid, string $id)
    {
        try {
            $taxCodes = QuickbooksQuery::getInstance()
              ->setRealmID($realmid)
              ->list_tax_codes($id);

            echo json_encode($taxCodes, JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            Error::response("Unable to retrieve QB Tax Code with Id=$id.", $e);
        }
    }


}
