<?php

namespace Controllers\QuickBooks;

use Models\QuickbooksTransfer;
use Core\QuickbooksConstants as QBO;
use Core\ErrorResponse as Error;
use Exception;
use InvalidArgumentException;
use QuickBooksOnline\API\Exception\SdkException;
use ReflectionException;
use QuickBooksOnline\API\Exception\IdsException;

/**
 * Controller to accomplish QBO Transfer related tasks.
 *
 * @category  Controller
*/
class QBTransferCtl
{
    /**
     * Return details of the QBO Transfer identified by $id
     *
     * @param string $realmid The company ID for the QBO company.
     * @param string $id
     * @return void Output is echo'd directly to response
     */
    public static function read_one(string $realmid, string $id)
    {
        try {
            $model = QuickbooksTransfer::getInstance()
              ->setRealmID($realmid)
              ->setId($id);

            echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
        } catch (\Exception $e) {
            Error::response("Unable to retrieve QB Transfer with id=$id.", $e);
        }
    }

    /**
     * Delete from QBO the transfer identified by $id
     *
     * On success the PHP call exits with HTTP status 200 and a message confirming success.
     * If this fails the PHP call exits with HTTP status 400 and a message describing the error.
     * @param string $realmid The company ID for the QBO company.
     * @param int $id The QBO id, not the DocNumber
     * @return void Output is echoed directly to response
     */
    public static function delete(string $realmid, int $id)
    {

        try {

            $model = QuickbooksTransfer::getInstance()
              ->setId($id)
              ->setRealmID($realmid);

            if ($model->delete()) {
                echo json_encode(
                    array(
                    "message" => "Transfer with id=$id was deleted.",
                    "id" => $id),
                    JSON_NUMERIC_CHECK
                );
            }

        } catch (\Exception $e) {
            Error::response("Unable to delete QB Transfer with id=$id.", $e);
        }
    }

    /**
     * Create a QBO transfer from data supplied via http POST
     *
     * @param string $realmid The company ID for the QBO company.
     * @return void Output is echoed directly to response
     *
     */
    public static function create(string $realmid)
    {

        try {

            $data = json_decode(file_get_contents("php://input"));

            QBTransferCtl::CheckDateAndAmountParameters($data);

            if (!isset($data->fromAccount)) {
                throw new InvalidArgumentException("'fromAccount' property is missing from POST body.");
            } elseif (!isset($data->toAccount)) {
                throw new InvalidArgumentException("'toAccount' property is missing from POST body.");
            } elseif ($data->toAccount == $data->fromAccount) {
                throw new InvalidArgumentException("'fromAccount' must be different from 'toAccount'.");
            } elseif (floatval($data->amount) <= 0) {
                throw new InvalidArgumentException("'amount' property must be greater than zero.");
            }

            QBTransferCtl::createTransfer(
                $realmid,
                $data->txnDate,
                $data->fromAccount,
                $data->toAccount,
                floatval($data->amount),
                isset($data->privateNote) ? $data->privateNote : ''
            );

        } catch (Exception $e) {
            Error::response("Unable to create Transfer in Quickbooks.", $e);
        }
    }
    /**
     * Create a QBO transfer from data supplied via http POST
     *
     * A positive amount is a transfer from interco to 'Paid by Parent'.
     * A negative amount is a transfer to interco from 'Paid by Parent'.
     * @param string $realmid The company ID for the QBO company.
     * @return void Output is echoed directly to response
     *
     */
    public static function create_enterprises_interco(string $realmid)
    {

        try {

            if ($realmid != QBO::ENTERPRISES_REALMID) {
                throw new Exception("Not implemented in Charity, this endpoint exists for Enterprises QuickBooks only.");
            }

            $data = json_decode(file_get_contents("php://input"));

            QBTransferCtl::CheckDateAndAmountParameters($data);

            if ($data->amount > 0) {
                $toAccount = QBO::AUEW_PAIDBYPARENT_ACCOUNT;
                $fromAccount = QBO::AUKW_INTERCO_ACCOUNT;
            } else {
                $fromAccount = QBO::AUEW_PAIDBYPARENT_ACCOUNT;
                $toAccount = QBO::AUKW_INTERCO_ACCOUNT;
            }

            QBTransferCtl::createTransfer(
                $realmid,
                $data->txnDate,
                $fromAccount,
                $toAccount,
                $data->amount,
                isset($data->privateNote) ? $data->privateNote : ''
            );

        } catch (\Exception $e) {
            Error::response("Unable to create interco transfer in Quickbooks.", $e);
        }
    }

    /**
     * Check that date aand amount parameters are provided
     * @param mixed $data HTTP post body, decoded from JSON format.
     * @return true
     * @throws InvalidArgumentException Thrown if a http parameter is missing or invalid
     */
    private static function CheckDateAndAmountParameters($data): true
    {
        if (!isset($data->txnDate)) {
            throw new InvalidArgumentException("'txnDate' property is missing from POST body.");
        } elseif (!\Core\DatesHelper::validateDate($data->txnDate)) {
            throw new InvalidArgumentException("'txnDate' property is not in the correct format. Value provided: $data->txnDate, expect yyyy-mm-dd format.");
        } elseif (!isset($data->amount)) {
            throw new InvalidArgumentException("'amount' property is missing from POST body.");
        } elseif ($data->amount == 0) {
            throw new InvalidArgumentException("'amount' property must be non-zero.");
        }

        return true;
    }

    /**
     * Create the transfer in QBO.
     * @param string $realmid The company ID for the QBO company.
     * @param string $txnDate The date the transfer occurred.
     * @param int $fromAccount The debit account
     * @param int $toAccount The credit account
     * @param float $amount The amount of money transferred
     * @param string $privateNote A memo about the transaction, optional.
     * @return void
     * @throws Exception
     * @throws SdkException
     * @throws ReflectionException
     * @throws IdsException
     */
    private static function createTransfer(
        string $realmid,
        string $txnDate,
        int $fromAccount,
        int $toAccount,
        float $amount,
        string $privateNote = ''
    ): void {
        $result = QuickbooksTransfer::getInstance()
        ->setRealmID($realmid)
        ->setTxnDate($txnDate)
        ->setFromAccount($fromAccount)
        ->setToAccount($toAccount)
        ->setPrivateNote($privateNote)
        ->setAmount($amount)
        ->create();

        if ($result) {
            echo json_encode(
                array("message" => "Transfer has been added for " . $txnDate . ".",
                    "id" => $result->Id)
            );
        }

    }
}
