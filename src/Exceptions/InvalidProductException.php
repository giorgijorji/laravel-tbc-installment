<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class InvalidProductException
 */
class InvalidProductException extends Exception
{
    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        Log::debug('Product structure or contents are invalid. Check for product you are adding!');
    }
}
