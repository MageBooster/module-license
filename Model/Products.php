<?php
declare(strict_types=1);

namespace MageBooster\License\Model;

/**
 * Paid products that need a serial. Each paid module adds itself in its own di.xml:
 * <argument name="products"><item name="currency-rates" xsi:type="string">Currency Rates (TCMB)</item></argument>
 */
class Products
{
    /**
     * @param array<string, string> $products product code => label
     */
    public function __construct(private array $products = [])
    {
    }

    /**
     * @return array<string, string>
     */
    public function getAll(): array
    {
        return $this->products;
    }
}
