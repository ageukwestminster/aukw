<?php

namespace Controllers\QuickBooks;

use Models\QuickbooksQuery;
use Core\ErrorResponse as Error;

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
    QBEntityCtl::read_all_impl($realmid, 'vendor', 'DisplayName');
  }
  /**
   * List name and id of all the QB Vendors
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_customers(string $realmid){  
    QBEntityCtl::read_all_impl($realmid, 'customer', 'DisplayName');
  }
  /**
   * List name and id of all the QB Vendors
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_accounts(string $realmid){  
    QBEntityCtl::read_all_impl($realmid, 'account', 'FullyQualifiedName');
  }
    /**
   * List name and id of all the QB Classes
   * @param string $realmid The company ID for the QBO company.
   * @return void Output is echo'd directly to response 
   */
  public static function read_all_classes(string $realmid){  
    QBEntityCtl::read_all_impl($realmid, 'class', 'FullyQualifiedName');
  }

    /**
   * List name and id of all the QB Entities of the given type
   * @param string $realmid The company ID for the QBO company.
   * @param string $type The type of IPPEntity
   * @param string $nameProperty The name of th eproperty to call when simplifying the IPPEntity
   * @return void Output is echo'd directly to response, in JSON. if the http parameter 'raw' 
   * is provided then the output is just the raw QBO response.
   */
  private static function read_all_impl(string $realmid, string $type, string $nameProperty) : void{  

    try {
      $entities = QuickbooksQuery::getInstance()
        ->setRealmID($realmid)
        ->list_entities($type);

      // If the raw http parameter is set then return the QBO data without changing it
      if(isset($_GET['raw'])) {
        echo json_encode($entities, JSON_NUMERIC_CHECK);
        exit;
      }

      if ($type == 'account') {
        $entities = array_map(fn($entity) => [
          "id" => $entity->Id,
          "value" => $entity->{'FullyQualifiedName'},
          "type" => $entity->{'AccountType'}
        ] , array_values($entities));
      } else {
        $entities = array_map(fn($entity) => [
          "id" => $entity->Id,
          "value" => $entity->{$nameProperty}
        ] , array_values($entities));
      }

      QBEntityCtl::sortByLowerCaseElement($entities, 'value');

      if ($type == 'class') {
        // The class ID is a very long number and can get rounded if JSON_NUMERIC_CHECK is used
        echo json_encode($entities);
      } else {
        echo json_encode($entities, JSON_NUMERIC_CHECK);
      }

    } catch (\Exception $e) {
      Error::response("Unable to obtain list of entities from QuickBooks.", $e);
    }
  }

  /**
   * Sort an array by the lower case value of the given element.
   * @param array &$array The array to sort, passed by reference.
   * @param string $elementName The name of the array element to sort by.
   * @return true 
   */
  private static function sortByLowerCaseElement(array &$array, string $elementName) : true {
    return usort(
      $array, 
      fn($a, $b) => strtolower($a[$elementName]) <=> strtolower($b[$elementName])
    );
  }

}