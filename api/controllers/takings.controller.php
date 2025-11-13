<?php

namespace Controllers;

use Core\ErrorResponse as Error;
use Exception;

/**
 * Controller to accomplish Takings related tasks: Creation, Retrieval, Updating and Deleting.
 *
 * @category  Controller
*/
class TakingsCtl
{
    /**
     * Return details of a Takings entry identified by $id
     *
     * @param int $id
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function read_one(int $id): void
    {
        try {
            $model = new \Models\Takings();
            $model->id = $id;

            echo json_encode($model->readone(), JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Error retrieving details of Takings with id=$id.", $e);
        }
    }

    /**
     * List all takings that have the 'quickbooks' property matching the given value.
     *
     * @param int $quickbooks
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function read_by_quickbooks_status(int $quickbooks): void
    {
        try {
            $model = new \Models\Takings();

            echo json_encode($model->read_by_quickbooks_status($quickbooks), JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Error retrieving details of all Takings with the given 'quicklbooks' value.", $e);
        }
    }

    /**
     * List all takings that have the 'shopid' property matching the given value.
     *
     * @param int $shopid
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function read_by_shop(int $shopid)
    {
        try {
            $model = new \Models\Takings();

            echo json_encode($model->read_by_shop($shopid), JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Error retrieving details of all Takings with shopid=$shopid.", $e);
        }
    }

    /**
     * Output the single Takings that is the most recent takings that also has the 'shopid'
     * property matching the given value.
     *
     * @param int $shopid
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function read_most_recent($shopid)
    {
        try {
            $model = new \Models\Takings();

            echo json_encode($model->read_most_recent($shopid), JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Error retrieving details of most recent Takings.", $e);
        }
    }

    /**
     * Add a new Takings entry to the database. Parameters are supplied via POST data.
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function create(): void
    {
        try {
            $model = new \Models\Takings();
            $data = json_decode(file_get_contents("php://input"));
            TakingsCtl::transferParameters($data, $model);

            // INSERT the row into the database
            $model->create();

            echo json_encode(
                array(
                "message" => "New takings with id=$model->id was created for $model->date.",
                "id" => $model->id
        ),
                JSON_NUMERIC_CHECK
            );

        } catch (Exception $e) {
            Error::response("Unable to add takings to database. Possibly a duplicate?", $e);
        }
    }

    /**
     * Update an existing Takings entry in the database with new data. Parameters are supplied via POST data.
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function update($id): void
    {
        try {
            $model = new \Models\Takings();
            $model->id = $id;

            $data = json_decode(file_get_contents("php://input"));
            TakingsCtl::transferParameters($data, $model);

            if ($model->update()) {
                echo json_encode(
                    array(
                        "message" => "Takings with id=$model->id was updated.",
                        "id" => $model->id
                    ),
                    JSON_NUMERIC_CHECK
                );
            } else {
                throw new Exception("Updating takings with id=$id failed for unknown reason.");
            }
        } catch (Exception $e) {
            Error::response("Unable to update takings with id=$id in database.", $e);
        }
    }

    /**
     * Delete the Takings object from the database that matches the given $id.
     *
     * @param int $id
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function delete(int $id)
    {
        try {
            $model = new \Models\Takings();
            $model->id = $id;

            if ($model->delete()) {
                echo json_encode(
                    array(
                    "message" => "Takings with id=$model->id was deleted.",
                    "id" => $model->id),
                    JSON_NUMERIC_CHECK
                );
            } else {
                throw new Exception("Deleting takings with id=$id failed for unknown reason.");
            }
        } catch (Exception $e) {
            Error::response("Unable to delete takings with id=$id in database.", $e);
        }
    }

    /**
     * PATCH the takings with the given id.
     * At present this only works for the quickbooks property.
     *
     * @param int $id The database id of the takings object.
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function patch(int $id)
    {
        try {
            $data = json_decode(file_get_contents("php://input"));
            if (isset($data->quickbooks)) {

                $model = new \Models\Takings();
                $model->id = $id;
                $model->quickbooks = empty($data->quickbooks) ? 0 : $data->quickbooks;

                if ($model->patch_quickbooks()) {
                    echo json_encode(
                        array(
                        "message" => "Takings with id=$model->id was patched to set QuickBooks to "
                        . $model->quickbooks .".",
                        "id" => $model->id),
                        JSON_NUMERIC_CHECK
                    );
                } else {
                    Error::response("Patching takings with id=$id failed for unknown reason.");
                }
            }
        } catch (Exception $e) {
            Error::response("Unable to patch takings with id=$id in database.", $e);
        }
    }

    /**
     * Helper function to copy takings property data into Model.
     * @see TakingsCtl::create()
     * @see TakingsCtl::update()
     *
     * @param object $data The supplied data of the new/updated takings object
     * @param \Models\Takings $model
     *
     * @return void
     *
     */
    private static function transferParameters($data, \Models\Takings $model)
    {
        if (isset($data->date)) {
            $model->date = $data->date;
        } else {
            throw new Exception("Takings 'date' missing");
        }
        if (isset($data->shopid)) {
            $model->shopid = $data->shopid;
        } else {
            throw new Exception("Takings 'shopid' missing");
        }
        $model->clothing_num = empty($data->clothing_num) ? 0 : $data->clothing_num;
        $model->brica_num = empty($data->brica_num) ? 0 : $data->brica_num;
        $model->books_num = empty($data->books_num) ? 0 : $data->books_num;
        $model->linens_num = empty($data->linens_num) ? 0 : $data->linens_num;
        $model->donations_num = empty($data->donations_num) ? 0 : $data->donations_num;
        $model->other_num = empty($data->other_num) ? 0 : $data->other_num;
        $model->rag_num = empty($data->rag_num) ? 0 : $data->rag_num;
        $model->clothing = empty($data->clothing) ? 0 : $data->clothing;
        $model->brica = empty($data->brica) ? 0 : $data->brica;
        $model->books = empty($data->books) ? 0 : $data->books;
        $model->linens = empty($data->linens) ? 0 : $data->linens;
        $model->donations = empty($data->donations) ? 0 : $data->donations;
        $model->other = empty($data->other) ? 0 : $data->other;
        $model->rag = empty($data->rag) ? 0 : $data->rag;
        $model->customers_num_total = empty($data->customers_num_total) ? 0 : $data->customers_num_total;
        $model->cash_to_bank = empty($data->cash_to_bank) ? 0 : $data->cash_to_bank;
        $model->credit_cards = empty($data->credit_cards) ? 0 : $data->credit_cards;
        $model->operating_expenses = empty($data->operating_expenses) ? 0 : $data->operating_expenses;
        $model->volunteer_expenses = empty($data->volunteer_expenses) ? 0 : $data->volunteer_expenses;
        $model->other_adjustments = empty($data->other_adjustments) ? 0 : $data->other_adjustments;
        $model->cash_to_charity = empty($data->cash_to_charity) ? 0 : $data->cash_to_charity;
        $model->cash_difference = empty($data->cash_difference) ? 0 : $data->cash_difference;
        $model->comments = $data->comments;
        $model->quickbooks = empty($data->quickbooks) ? 0 : $data->quickbooks;
        $model->rags_paid_in_cash = empty($data->rags_paid_in_cash) ? 0 : $data->rags_paid_in_cash;

    }
}
