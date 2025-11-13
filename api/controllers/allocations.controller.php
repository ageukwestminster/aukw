<?php

namespace Controllers;

use Exception;
use Core\ErrorResponse as Error;
use Core\QuickbooksConstants as QBO;
use Models\Allocations;
use Models\Allocation;

/**
 * Controller to acomplish Allocations related CRUD tasks
 * Allocations are assignments of portions of employee salaries to projects (aka QBO classes)
 *
 * @category  Controller
 */
class AllocationsCtl
{
    /**
     * Return details of all Allocations
     *
     * @return void Output is echo'd directly to response
     */
    public static function read_all(): void
    {
        try {
            // Note alsence of JSON_NUMERIC_CHECK to preserve class as string, despite being numeric
            echo json_encode(Allocations::getInstance()->read());
        } catch (Exception $e) {
            Error::response("Error retrieving details of all Allocations.", $e);
        }
    }

    /**
     * Return details of one Allocation
     * @param int $quickbooksId The QBO id of the Employee
     * @param string $class The QBO class ID
     * @return void Output is echo'd directly to response
     */
    public static function read_one(int $quickbooksId, string $class): void
    {
        try {
            // Note alsence of JSON_NUMERIC_CHECK to preserve class as string, despite being numeric
            /** @disregard Intelephense error on next line */
            echo json_encode(
                Allocation::getInstance()
                ->setQuickbooksId($quickbooksId)
                ->setClass($class)
                ->readOne(),
            );
        } catch (Exception $e) {
            Error::response("Error retrieving details of one Allocation.", $e);
        }
    }

    /**
     * Return array of allocations for one employee
     * @param int $payrollNumber The payroll number of the Employee
     * @return void Output is echo'd directly to response
     */
    public static function read_one_payrollnumber(int $payrollNumber): void
    {
        try {
            // Note alsence of JSON_NUMERIC_CHECK to preserve class as string, despite being numeric
            echo json_encode(Allocations::getInstance()->read($payrollNumber));
        } catch (Exception $e) {
            Error::response(
                "Error retrieving details of Allocations for employee with payroll number=" .
                $payrollNumber .
                ".",
                $e,
            );
        }
    }

    /**
     * Delete from the database all the Allocations
     * @return void Output is echo'd directly to response
     *
     */
    public static function delete(): void
    {
        try {
            if (Allocations::getInstance()->delete()) {
                echo json_encode(
                    [
                    "message" => "Allocations table has been cleared.",
          ],
                    JSON_NUMERIC_CHECK,
                );
            } else {
                throw new Exception("Deleting allocations failed for unknown reason.");
            }
        } catch (Exception $e) {
            Error::response("Error deleting allocations.", $e);
        }
    }

    /**
     * Delete from the database all the Allocations
     * @param int $payrollNumber The payroll number of the Employee
     * @return void Output is echo'd directly to response
     *
     */
    public static function deleteOne(int $payrollNumber): void
    {
        try {
            if (isset($payrollNumber) === false) {
                throw new Exception(
                    "Payroll number not provided for deletion of allocations.",
                );
            }

            if (Allocations::getInstance()->deleteByPayrollNumber($payrollNumber)) {
                echo json_encode(
                    [
                    "message" =>
                      "Allocations for employee with payroll number = " .
                      $payrollNumber .
                      " in table have been cleared.",
          ],
                    JSON_NUMERIC_CHECK,
                );
            } else {
                throw new Exception(
                    "Deleting allocations for single employee failed for unknown reason.",
                );
            }
        } catch (Exception $e) {
            Error::response("Error deleting allocations.", $e);
        }
    }

    /**
     * Add a new set of allocations to the database.
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function create(): void
    {
        try {
            if (Allocations::getInstance()->delete() === false) {
                throw new Exception(
                    "Failed to clear existing allocations before insert.",
                );
            }

            $data = json_decode(file_get_contents("php://input"));
            $model = Allocation::getInstance();

            foreach ($data as $item) {
                // Account can be omitted in the input, so determine it here
                $account = isset($item->account)
                  ? $item->account
                  : QBO::payrollAccountFromEmployeeStatus($item->isShopEmployee);

                $model
                  ->setQuickbooksId($item->quickbooksId)
                  ->setPayrollNumber($item->payrollNumber)
                  ->setPercentage($item->percentage)
                  ->setAccount($account)
                  ->setClass($item->class)
                  ->setIsShopEmployee($item->isShopEmployee);

                if ($model->create() !== true) {
                    throw new Exception(
                        "Failed to insert new allocation for payroll number " .
                        $item->payrollNumber,
                    );
                }
            }

            echo json_encode(
                [
                "message" => "New allocations have been created.",
        ],
                JSON_NUMERIC_CHECK,
            );
        } catch (Exception $e) {
            Error::response("Error inserting new Allocations.", $e);
        }
    }

    /**
     * Add allocation(s) to the database. The provided allocation(s) are appended to existing ones.
     * The POST data object must be an array.
     *
     * @return void Output is echo'd directly to response
     *
     */
    public static function append(): void
    {
        try {
            if (!Allocations::getInstance()->verify()) {
                throw new Exception(
                    "Percentage totals do not equal 100% before append, aborting.",
                );
            }

            $data = json_decode(file_get_contents("php://input"));

            foreach ($data as $item) {
                $model = Allocation::getInstance();

                $allocation = $model
                  ->setQuickbooksId($item->quickbooksId)
                  ->setClass($item->class)
                  ->readOne();

                // Account can be omitted in the input, so determine it here
                $account = isset($item->account)
                  ? $item->account
                  : QBO::payrollAccountFromEmployeeStatus($item->isShopEmployee);

                $model
                  ->setPayrollNumber($item->payrollNumber)
                  ->setPercentage($item->percentage)
                  ->setAccount($account)
                  ->setIsShopEmployee($item->isShopEmployee);
                if ($allocation !== null && !empty($allocation)) {
                    $result = $model->update();
                } else {
                    $result = $model->create();
                }

                if ($result !== true) {
                    throw new Exception(
                        "Failed to insert new allocation for payroll number " .
                        $item->payrollNumber,
                    );
                }
            }

            if (!Allocations::getInstance()->verify()) {
                throw new Exception(
                    "Percentage totals do not equal 100% after append.",
                );
            }

            echo json_encode(
                [
                "message" => "Allocation(s) have been appended.",
        ],
                JSON_NUMERIC_CHECK,
            );
        } catch (Exception $e) {
            Error::response("Error appending new Allocation(s).", $e);
        }
    }
}
