<?php

namespace Controllers;

use \Core\ErrorResponse as Error;
use \Models\Rules;
use Exception;
/**
 * Controller to accomplish trading Rule based tasks. 
 *
 * @category  Controller
*/
class RuleCtl{


  /**
   * Return details of all Rules
   * 
   * @return void Output is echo'd directly to response 
   */
  public static function read_all():void{  
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
  protected static function read_all_raw():array{  
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
  public static function interco_trade_from_rules(string $realmid):void{  
    try {

      $data = json_decode(file_get_contents("php://input"));

      if ( $data == '' )
      {
        throw new \InvalidArgumentException("Empty POST body.");
      } else if (!\Core\DatesHelper::validateDate($data->date) ) {
        throw new \InvalidArgumentException("'date' property is not in the correct format. Value provided: $data->date, expect yyyy-mm-dd format.");
      } else if (!isset($data->amount) || is_null($data->amount)) {
        throw new \InvalidArgumentException("'amount' property is missing from POST body or is set to NULL.");
      }


      /*
      id: this.id,
      date: this.date,
      type: this.type.value,
      docnumber: this.docnumber,
      name: this.name.value,
      emp_name: this.emp_name.value,
      memo: this.memo,
      account: this.account.value,
      amount: this.amount,
      taxable: this.taxable ? 'Yes' : 'No',
      */

      // TODO use $realmid

      $rules = Rules::getInstance()
        ->read();

      $memo = strtolower(($data->memo) ?? '');

      foreach ($rules as $rule) {
        
        if (($data->account == $rule['search_account'] || is_null($rule['search_account']))
            && ($data->name == $rule['search_entity'] || $rule['search_entity'] == NULL)
            && ($data->docnumber == $rule['search_docnumber'] || $rule['search_docnumber'] == NULL)
            && (is_null($rule['search_memo']) || str_contains($memo, $rule['search_memo']))  
            ) {
              // match found
              // create interco trade
              echo json_encode(array(
                  "date" => $data->date,
                  "docnumber" => $data->docnumber,
                  "name" => $rule['entity'],
                  "employee" => $data->employee,
                  "memo" => $rule['memo'],
                  "account" => $rule['account'],
                  "amount" => $data->amount,
                  "taxable" => $rule['taxable'],
                  "description" => $data->memo?$data->memo:$rule['description']                
              ), JSON_NUMERIC_CHECK);
              return;
            }
      }
      

    } catch (Exception $e) {
      Error::response("Error retrieving details of the new interco trade.", $e);
    }
  }


}