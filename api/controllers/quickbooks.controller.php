<?php

namespace Controllers;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2LoginHelper;
use QuickBooksOnline\API\Facades\JournalEntry;
use Models\QuickbooksToken;
use DateTime;
use DateTimeZone;

class QuickbooksCtl{

  private static function config() {
      return array(
        'auth_mode' => 'oauth2',
        'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
        'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
        'ClientID' => 'ABfKBoCDvYwfccfV7X48SxjS9DewKuKXSujBMjSHB7X9BUcoyi',
        'ClientSecret' => '11wbu5o2Zr6uXkVQH9jc290sig7pqeernTSHUba9',
        'scope' => 'com.intuit.quickbooks.accounting',
        'redirectURI' => 'https://dac5f5462132.ngrok.io/api/qb/callback',
        'response_type' => 'code',
        'state' => 'TEKP56'
      );
  }


  public static function read_journal($id){  

    // Prep Data Services
    $dataService = DataService::Configure(array(
        'auth_mode' => 'oauth2',
        'ClientID' => "ABfKBoCDvYwfccfV7X48SxjS9DewKuKXSujBMjSHB7X9BUcoyi",
        'ClientSecret' => "11wbu5o2Zr6uXkVQH9jc290sig7pqeernTSHUba9",
        'accessTokenKey' =>
        'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..lVDrpRnAoiRr5Y04EmLeMw.ipHfOSYrjhjih3CLxy8yK_qAy0XcWxl7ErHW7blqw5OdezZ4D68bIjebLqtkXGrGfzltlLdvVhA9S9ACH3i0eE9uYyKsST0VJRhDNH8bn_vdcBJ6uVo0-f9Co_RTszsOoOEy8dsdapwUzfo_IQCvxgTMXCpCU0T6ccc4v58Kl-C0G3B22Ihzi5DdzHH7Mk3Mot7Whw9Y3C4CTKl6PleLsGzi3fXg6KVrivYdgngZwICg_wcloW657ojharPqyysf6h6Pdkt8Z2hHcsPH3BS94kZe5hICKCAeJkY7YUcSzGyHDtOdKl9bHYEgdm-ysuGbvRpRoRn05gj0k80iyLX3n0wIo9T0xR8Onj4Kz18tOHKPe11iNc7eBBmINoLQgbTgUDMkdpL66O9TPyzzQrX6NJANTZaJsqq18aAWBaETAi4bRFcdNUY03Zm3qGZqwFOd0Jllgd9LeHmTCa-7btEYTOw8yDzo7zDnvGpyXOLeTheUOmTe6qRv0tgFWh5VlL3leokIzWcwWHKv5zYDaeBm5KUbRH5oNZiI33V9UOs1-FEH088tPnfEW1DAj00BqV-Pz9FB_8CRzqFFBXQ0kW6aY6nkSfVOFwTgNORdEJCpzeYLLv1RpyeOEOuVKVsH2glZkz24luusAG6TOpgKVB2v3UavKGYstQbIJuRCUCthYhds5ftANTU44dM3dUkTOy9BYGDmOBDe5x3TXofR_gv185IOn0GrC2ofwoY5F8emEtY.yvIbL5u1sxozbKnAAvLJAw',
        'refreshTokenKey' => "AB11628800598nlBfrph7Hr5Us4bCYknraDkpO15T4vl8lrx9N",
        'QBORealmID' => "9130350604308576",
        'baseUrl' => "Production"
    ));
    $dataService->setLogLocation("B:\\logs");
    $dataService->throwExceptionOnError(true);
    $dataService->forceJsonSerializers();
    $journayentry = $dataService->FindbyId('journalentry', $id);
    $error = $dataService->getLastError();
    if ($error) {
        echo "The Status code is: " . $error->getHttpStatusCode() . "\n";
        echo "The Helper message is: " . $error->getOAuthHelperError() . "\n";
        echo "The Response message is: " . $error->getResponseBody() . "\n";
    }
    else {
        //echo "Created Id={$journayentry->Id}. Reconstructed response body:\n\n";
        echo json_encode($journayentry, JSON_NUMERIC_CHECK);
        //$xmlBody = XmlObjectSerializer::getPostXmlFromArbitraryEntity($journayentry, $urlResource);
        //echo $xmlBody . "\n";
    }
  }


  public static function oauth2_begin(){
    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
    $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();
    header('Location: '. $authorizationCodeUrl);
    exit(0);
  }

  public static function oauth2_callback(){

    $config = QuickbooksCtl::config();
    $dataService = DataService::Configure($config);
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $code = $_GET['code'];
    $state = $_GET['state'];
    $realmId = $_GET['realmId'];

    if ($state != $config['state']) {
      http_response_code(422);  
      echo json_encode(
        array("message" => "Unable to proceed with QB callback: 'state' does not match initial value.")
      );
    }

    $accessTokenObj = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);

    $dataService->updateOAuth2Token($accessTokenObj);

    $model = new QuickbooksToken();
    $model->iduser=11;
    $model->read();
    QuickbooksCtl::store_tokens($model, $accessTokenObj);
  }

  public static function oauth2_refresh(){

    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $model = new QuickbooksToken();
  
    $model->read();
    $accessTokenObj = $OAuth2LoginHelper->
                refreshAccessTokenWithRefreshToken($model->refreshtoken);

    $dataService->updateOAuth2Token($accessTokenObj);                
    QuickbooksCtl::store_tokens($model, $accessTokenObj);
  }

  public static function oauth2_revoke(){

    $dataService = DataService::Configure(QuickbooksCtl::config());
    $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();

    $model = new QuickbooksToken();
    $model->read();
    
    $result = $OAuth2LoginHelper->revokeToken($model->accesstoken);

    if ($result) {
      $model->delete();
      echo json_encode(
        array(
          "message" => "All QB tokens revoked."
        )
      , JSON_NUMERIC_CHECK);
    } else {
      http_response_code(422);  
      echo json_encode(
        array("message" => "Unable to revoke QB tokens.")
      );
    }
    
    
  }

  private static function store_tokens($model, $accessTokenObj){

    if ($model->accesstoken) {
      $isUpdate = true;
    } else {
      $isUpdate = false;
    }

    $model->accesstoken = $accessTokenObj->getAccessToken();
    $model->refreshtoken = $accessTokenObj->getRefreshToken();

    // Expiries in the QB world are in UTC. Convert to local time
    // before saving to the database. Otherwise during BST the time
    // will be wrong by 1 hour
    $expiry = $accessTokenObj->getAccessTokenExpiresAt();
    $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
    $displayDate->setTimezone(new DateTimeZone('Europe/London'));
    $model->accesstokenexpiry = $displayDate->format('Y-m-d H:i:s');

    $expiry = $accessTokenObj->getRefreshTokenExpiresAt();
    $displayDate = new DateTime($expiry, new DateTimeZone('UTC'));
    $displayDate->setTimezone(new DateTimeZone('Europe/London'));
    $model->refreshtokenexpiry = $displayDate->format('Y-m-d H:i:s');

    if ($isUpdate) {
      return $model->update();
    } else {
      return $model->insert();
    }
  }
}