<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface LaravelTbcInstallmentInterface
 */
interface ProductModelInterface
{
    /**
     * @param float $priceTotal
     * @return bool
     */
    public function validateTotalPrice(float $priceTotal): bool;

    /**
     * @param array $product
     * @return bool
     */
    public function validateProduct(array $product): bool;

    /**
     * @param array $product
     * @return bool
     */
    public function pushProduct(array $product): bool;

    /**
     * @param array $products
     * @return bool
     */
    public function pushProducts(array $products): bool;

    /**
     * @return float
     */
    public function getProductsTotalPrice(): float;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getProducts(): Collection;
}
