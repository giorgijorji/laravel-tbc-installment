<?php
declare(strict_types=1);

namespace Giorgijorji\LaravelTbcInstallment\Interfaces;

/**
 * Interface LaravelTbcInstallmentInterface
 */
interface LaravelTbcInstallmentInterface
{
    /**
     * @param array $product
     */
    public function addProduct(array $product): void;

    /**
     * @param array $products
     */
    public function addProducts(array $products): void;

    /**
     * @return string
     */
    public function getRedirectUri(): string;

    /**
     * @return string
     */
    public function getSessionId(): string;

    /**
     * @param string $invoiceId
     * @param float $priceTotal
     * @return array
     */
    public function applyInstallmentApplication(string $invoiceId, float $priceTotal): array;

    /**
     * @param string $invoiceId
     * @param string $sessionId
     * @param float $priceTotal
     * @return array
     */
    public function confirm(string $invoiceId, string $sessionId, float $priceTotal): array;

    /**
     * @param string $sessionId
     * @return array
     */
    public function cancel(string $sessionId): array;

    /**
     * @return array
     */
    public function getProducts(): array;
}
