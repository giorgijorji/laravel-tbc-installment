<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class InvalidProductPriceException
 */
class InvalidProductPriceException extends Exception
{
    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        Log::debug('Product prices sum and total price are different!');
    }
}
