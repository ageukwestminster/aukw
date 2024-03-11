<?php

namespace Models;

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

/**
 * Dummay QBI Auth backend
 * @category Model
 */
class QuickbooksDummyAuthProvider{

    public static function getUserInfo() {
        $user = array();
        $user['sub'] = "3bdfb5a7-cbd0-4a0e-a801-74c560681fad";
        $user['givenName'] = "Neil";
        $user['familyName'] = "Carthy";
        $user['email'] = "neil.carthy@ageukwestminster.org.uk";
        $user['emailVerified'] = true;
        return $user;
    }

    public static function getToken($config) {
        $accessToken = "eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0.." . 
        "r_hR1esbJ9_UymS7jbTzbw.0Amw4Huayq1_AXUmMcT0orfUnOVDIDHmKN4qrSIQiQ5r4ZyKWAP--".
        "wufPZILYfm8FbAv2APFVHuO0vGG2E-yjtXqUqSu9L6G_auVxydW00kfBiItIcFC-hdaniX_Sfw9oKZ6".
        "hgMSvnYM6eWiIVSrQ-5odZkwl_xGgaebeMZmG5ASGbF-zqfnLUqkUpqcj0mXnpjji1Talf3LA5R8IVaaeD".
        "SX8vynRAn-MfD2uGCEJ5XfzL0OGmnt3biuZ-ryAMCMx0utzMVjryED_Cuj_aItvQEO38arIz-CnAkakj2-".
        "eMwu7m0izP6gwNP4osRUKES-dJHZQAazJo7zeKn73r9B1ZVpxpH61GYdZ_yxtUXS0S6c-E-_qcUWzwKlvZ".
        "3aU1RhYYzFT-O-UrhFeklpDR3VvsrYuJKaM_6FEZq2u96g57RGf9PCLFOVbHMUoVwZxzzeea2NhI5vyAxf".
        "Svw0c1NajkHXTsC8Kk_ZhtF60qv6FvKMo0hKHPK5I_aPlxHmtWIwMOIMFGJiXvE_nbL16hAtndL3LPP2ke".
        "kX7YwNxzaGLmM6USVgBcpIU_UkPW1ZNx_9UVQ3JkDM05zm5zM-D0_tOmHpkpno022Mab1gvX63IyEm_ntp".
        "rD2Pw4EbtewlT3E5YaJF_4QynF4nYerAFyKeCth6V5Xaaf1ZPs6c9yzT09aiveRq-H5FnKyEnwqiWnQPdr".
        "C_r8ee9DyNRK2_hbvo7-2QFKtHP9xNpu6nw2fCH0B1RlIDglWxVIp69sFcTE3zXPiOfyetIvD8B0ag8po7".
        "sE7ftl03DmgK2KAMS4_z3ezNAOAA6iIzHxWjhCxU5t54L0qNv114tjFHvJSlRozikuwt6eimZTzPSr-Zpw".
        "s4adq-Omiuzn-DH3Kp5liODcVC.8j9efXEXvrNq9a4xac-9uw";

        $token = new OAuth2AccessToken($config['ClientID'], $config['ClientSecret']);
        $token->updateAccessToken(3600,"AB117188720204p3gT5bi5N9kP7XJVrOm9SgVe07NvA2wpwJ3t"
                ,8726400,$accessToken);
        $token->setRealmID(\Core\Config::read('qb.realmid'));
        return $token;
    }
}