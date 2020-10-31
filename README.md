# Laravel TBC Installment

[![Latest Stable Version](https://img.shields.io/packagist/v/giorgijorji/laravel-tbc-installment.svg)](https://packagist.org/packages/giorgijorji/laravel-tbc-installment)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/giorgijorji/laravel-tbc-installment.svg)](https://packagist.org/packages/giorgijorji/laravel-tbc-installment)
[![Downloads Month](https://img.shields.io/packagist/dm/giorgijorji/laravel-tbc-installment.svg)](https://packagist.org/packages/giorgijorji/laravel-tbc-installment)


This package allows you to use TBC Installment in your Laravel application.
* Version v1.0.0
## Table of Contents

- [Installation](#installation)
- [Getting Access Token](#getting-access-token)
- [Environment](#environment)
- [Usage](#usage)
- [Result Codes](#result-codes)
- [TODO](#todo)
- [Credits](#credits)

## Installation

```
composer require giorgijorji/laravel-tbc-installment
```

#### For Laravel <= 5.4

If you're using Laravel 5.4 or lower, you have to manually add a service provider in your `config/app.php` file.
Open `config/app.php` and add `TbcPayServiceProvider` to the `providers` array.

```php
'providers' => [
    # Other providers
    Giorgijorji\LaravelTbcInstallment\TbcInstallmentServiceProvider::class,
],
```

Then run:

```
php artisan vendor:publish --provider="Giorgijorji\LaravelTbcInstallment\TbcInstallmentServiceProvider"
```

## Getting Access Token

In order to access Online Installment endpoints, merchant application should request access token with Oauth2 Client Credential flow. apiKey and apiSecret values should be passed as client_id and client_secret. This operation is used to verify registered developer app and grant general access to the Open API platform. To get your apiKey and apiSecret, follow instructions at developers.tbcbank.ge/get-started

## Environment
After getting apiKey, apiSecret, merchantKey and campaignId place them in `.env`.

Set your environment variables:
```
TBC_ENVIRONMENT=testing
TBC_INSTALLMENT_API_KEY=your_api_key
TBC_INSTALLMENT_API_SECRET=your_api_secret
TBC_INSTALLMENT_MERCHANT_KEY=your_merchant_key
TBC_INSTALLMENT_CAMPAIGN_ID=your_campaing_id
```

## Usage
```php
<?php

use Giorgijorji\LaravelTbcInstallment\LaravelTbcInstallment;

# Create new instance of LaravelTbcInstallment
$tbcInstallment = new LaravelTbcInstallment();
# Product Example | Array
$product = [
    'name' => "SampleProduct", // string - product name
    'price' => 12.33, // Value in GEL (decimal numbering); Note that if Quantity is more than 1, you must set total price
    'quantity' => 1, // integer - product quantity
];
# Call AddProduct

$tbcInstallment->addProduct($product);

/*
* @param string your invoiceId - 
* The unique value of your system that is attached to the application, for example, is initiated by you
* Application Id which is in your database.
* When a customer enters into an installment agreement on the TBC Installment Site, you will receive this InvoceId by email along with other details.
* invoiceId must identify the application on your side.
* 
* @param decimal total price of all Products
*/
$tbcInstallment->confirm(1, 12.33);

# After confirm you can get sessionId and redirect url to tbc installment web page

$sessionId = $tbcInstallment->getSessionId(); // string - session id for later use to cancel  installment
$redirectUri = $tbcInstallment->getRedirectUri(); // string - redirect uri to tbc installment webpage
# save session id to your database
# then you can simply call laravel redirect method
return redirect($redirectUri);

# Cancel Installment application example, $sessionId is previously saved sessionId
# $tbcInstallment->cancel($sessionId);

# That's all :)
```
## Result Codes

| Key | Value             | Description                                                                           |
|-----|-------------------|---------------------------------------------------------------------------------------|
| 200 | Ok                | Application Confirmed                                                                              |

## TODO
- Handle validation of products and other stuff, to add custom Exceptions for invalid requests

## Credits

- Any  suggestions or improvement requests accepted
- [Z3R0](https://github.com/giorgijorji)
