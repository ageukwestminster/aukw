<?php

namespace Controllers;

use \Models\QuickbooksQuery;

/**
 * Controller to accomplish QBO Employee related tasks. 
 *
 * @category  Controller
*/
class QBEntityCtl{

  /**
   * List name and id of all the QB Vendors
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_vendors(string $realmid){  
    $entities = QBEntityCtl::read_all_impl($realmid, 'vendor', 'DisplayName');
    echo json_encode($entities, JSON_NUMERIC_CHECK);
  }
  /**
   * List name and id of all the QB Vendors
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_customers(string $realmid){  
    $entities = QBEntityCtl::read_all_impl($realmid, 'customer', 'DisplayName');
    echo json_encode($entities, JSON_NUMERIC_CHECK);
  }
  /**
   * List name and id of all the QB Vendors
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_accounts(string $realmid){  
    $entities = QuickbooksQuery::getInstance()
    ->setRealmID($realmid)
    ->list_entities('account');
    //echo json_encode($entities, JSON_NUMERIC_CHECK);

    $entities = QBEntityCtl::read_all_impl($realmid, 'account', 'FullyQualifiedName');
    echo json_encode($entities, JSON_NUMERIC_CHECK);
  }
    /**
   * List name and id of all the QB Entities of the given type
   * @param string $realmid The company ID for the QBO company.
   * @param string $type The type of IPPEntity
   * @param string $nameProperty The name of th eproperty to call when simplifying the IPPEntity
   * @return array
   */
  private static function read_all_impl(string $realmid, string $type, string $nameProperty) : array{  

    try {
      $entities = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->list_entities($type);

        $simplifiedEntities = array_map(fn($entity) => [
          "Id" => $entity->Id,
          "Name" => $entity->{$nameProperty}
        ] , array_values($entities));

        usort(
          $simplifiedEntities, 
          fn($a, $b) => strtolower($a['Name']) <=> strtolower($b['Name'])
        );

        return $simplifiedEntities;
    } catch (\Exception $e) {
      http_response_code(400);   
      echo json_encode(
        array("message" => "Unable to obtain list of entities from QuickBooks.",
        "details" => $e->getMessage())
      );
      exit(1);
    }

  }

}