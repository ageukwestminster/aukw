<?php

namespace Controllers;

use Core\QuickbooksConstants as QBO;
use Core\ErrorResponse as Error;
use Models\Rules;
use Exception;

/**
 * Controller to accomplish trading Rule based CRUD tasks.
 * A trading rule is a set of criteria to match a transaction to create an intercompany trade.
 *
 * @category  Controller
*/
class RuleCtl
{
    /**
     * Return details of all Rules
     *
     * @return void Output is echo'd directly to response
     */
    public static function read_all(): void
    {
        try {
            $rules = RuleCtl::read_all_raw();

            echo json_encode($rules, JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            Error::response("Error retrieving details of all Rules.", $e);
        }
    }

    /**
     * Return details of all Rules
     *
     * @return array
     */
    protected static function read_all_raw(): array
    {
        try {
            return Rules::getInstance()
              ->read();

        } catch (Exception $e) {
            Error::response("Error retrieving details of all Rules.", $e);
        }
    }

    /**
     *
     *
     * @return void Output is echo'd directly to response.
     */
    public static function interco_trade_from_rules(string $realmid): void
    {
        try {

            $data = json_decode(file_get_contents("php://input"));

            if ($data == '') {
                throw new \InvalidArgumentException("Empty POST body.");
            } elseif (!\Core\DatesHelper::validateDate($data->date)) {
                throw new \InvalidArgumentException("'date' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
            } elseif (!isset($data->amount) || is_null($data->amount)) {
                throw new \InvalidArgumentException("'amount' property is missing from POST body or is set to NULL.");
            }

            $account = !isset($data->account) ? null : $data->account->id;
            $entity = !isset($data->name) ? null : $data->name->id;
            $employee = !isset($data->employee) ? null : $data->employee;
            $charity = ($realmid == QBO::CHARITY_REALMID) ? 1 : 0;

            $rules = Rules::getInstance()
              ->read();

            $memo = strtolower(($data->memo) ?? '');

            foreach ($rules as $rule) {

                if (($charity == $rule['charity'])
                    && ($account == $rule['search_account'] || is_null($rule['search_account']))
                    && ($entity == $rule['search_entity'] || $rule['search_entity'] == null)
                    && ($data->docnumber == $rule['search_docnumber'] || $rule['search_docnumber'] == null)
                    && (is_null($rule['search_memo']) || str_contains($memo, $rule['search_memo']))
                ) {
                    // match found
                    // create interco trade
                    echo json_encode(array(
                        "date" => $data->date,
                        "docnumber" => $data->docnumber,
                        "name" => is_null($rule['entity']) ? null : array("id" => $rule['entity']),
                        "employee" => $employee,
                        "memo" => $rule['memo'],
                        "account" => is_null($rule['account']) ? null : array("id" => $rule['account']),
                        "amount" => $data->amount,
                        "taxable" => $rule['taxable'],
                        "description" => $data->memo ? $data->memo : $rule['description']
                    ), JSON_NUMERIC_CHECK);
                    return;
                }
            }

            // If no rule matched then return an empty object
            echo json_encode((object)array(), JSON_NUMERIC_CHECK);

        } catch (Exception $e) {
            Error::response("Error retrieving details of the new interco trade at line " .$e->getLine().".", $e);
        }
    }


}
