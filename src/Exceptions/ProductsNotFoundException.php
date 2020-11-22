<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class ProductsNotFoundException
 */
class ProductsNotFoundException extends Exception
{
    /**
     * Report or log an exception.
     *
     * @return void
     */
    public function report()
    {
        Log::debug('Apply function used where products are empty!');
    }
}
