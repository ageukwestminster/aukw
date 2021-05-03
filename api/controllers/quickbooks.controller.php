<?php

namespace Controllers;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\JournalEntry;

class QuickbooksCtl{

  private static function config() {
      return array(
        'authorizationRequestUrl' => 'https://appcenter.intuit.com/connect/oauth2',
        'tokenEndPointUrl' => 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
        'client_id' => 'ABfKBoCDvYwfccfV7X48SxjS9DewKuKXSujBMjSHB7X9BUcoyi',
        'client_secret' => '11wbu5o2Zr6uXkVQH9jc290sig7pqeernTSHUba9',
        'oauth_scope' => 'com.intuit.quickbooks.accounting',
        'oauth_redirect_uri' => 'http://localhost:3000/callback.php',
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

}