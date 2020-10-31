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
     */
    public function confirm(string $invoiceId, float $priceTotal): void;

    /**
     * @param string $sessionId
     */
    public function cancel(string $sessionId): void;
}
