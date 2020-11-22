<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Models;

use Giorgijorji\LaravelTbcInstallment\Interfaces\ProductModelInterface;
use Illuminate\Support\Collection;

/**
 * Class ProductModel
 */
class ProductModel implements ProductModelInterface
{
    /** @var \Illuminate\Support\Collection */
    protected $products;

    /**
     * ProductModel constructor.
     */
    public function __construct()
    {
        $this->products = new Collection();
    }

    /**
     * Validate that provided price is equal to products collection price sum
     * @param float $priceTotal
     * @return bool
     */
    public function validateTotalPrice(float $priceTotal): bool
    {
        if ($this->getProductsTotalPrice() !== $priceTotal) {
            return false;
        }
        return true;
    }

    /**
     * Validate product structure
     * @param array $product
     * @return bool
     */
    public function validateProduct(array $product): bool
    {
        if (!array_key_exists('name', $product) || !array_key_exists('price', $product) || !array_key_exists('quantity', $product)) {
            return false;
        }

        if (!is_string($product['name'])) {
            return false;
        }

        if (!is_float($product['price'])) {
            return false;
        }

        if (!is_int($product['quantity'])) {
            return false;
        }

        return true;
    }

    /**
     * Push single product to products collection
     * @param array $product
     * @return bool
     */
    public function pushProduct(array $product): bool
    {
        if ($this->validateProduct($product)) {
            $this->products->push($product);
            return true;
        }
        return false;
    }

    /**
     * Push multiple products to products collection
     * @param array $products
     * @return bool
     */
    public function pushProducts(array $products): bool
    {
        foreach ($products as $product) {
            if (!$this->pushProduct($product)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get sum of product prices
     * @return float
     */
    public function getProductsTotalPrice(): float
    {
        return $this->products->sum('price');
    }

    /**
     * Get all products
     * @return \Illuminate\Support\Collection
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }
}
