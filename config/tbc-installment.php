<?php

return [

    /*
     * Test service url: https://test-api.tbcbank.ge
     * Production service url: https://api.tbcbank.ge
     * Base Path: /v1
     * More information at: https://developers.tbcbank.ge/docs/online-installments---test/1/overview
     * For apiKey and apiSecret please visit: developers.tbcbank.ge/get-started.
     * TBC installment environment : testing | production
     * */

    'environment' => env('TBC_ENVIRONMENT', 'testing'),

    /*
     * For apiKey and apiSecret please visit: developers.tbcbank.ge/get-started
     * TBC installment apiKey
     * */

    'apiKey' => env('TBC_INSTALLMENT_API_KEY', ''),

    /*
     * For apiKey and apiSecret please visit: developers.tbcbank.ge/get-started
     * TBC installment apiSecret
     * */

    'apiSecret' => env('TBC_INSTALLMENT_API_SECRET', ''),

    /*
     * TBC installment merchantKey
     * */

    'merchantKey' => env('TBC_INSTALLMENT_MERCHANT_KEY', ''),

    /*
     * TBC installment campaignId
     * For testing purpose use 191
     * */

    'campaignId' => env('TBC_INSTALLMENT_CAMPAIGN_ID', 191),
];
