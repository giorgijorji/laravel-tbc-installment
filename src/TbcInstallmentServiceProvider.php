<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment;

use Illuminate\Support\ServiceProvider;

/**
 * Class TbcInstallmentServiceProvider
 */
class TbcInstallmentServiceProvider extends ServiceProvider
{
    /**
     * Config publishing
     * boot
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/tbc-installment.php' => config_path('tbc-installment.php'),
        ], 'config');
    }

    /**
     * Ensure that only one instance of LaravelTbcInstallment running
     * register
     */
    public function register()
    {
        $this->app->singleton(LaravelTbcInstallment::class, function () {
            return new LaravelTbcInstallment();
        });
    }
}
