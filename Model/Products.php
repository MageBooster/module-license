<?php
/*
 * Copyright (c) 2026 WebWorks 101 OÜ (MageBooster). All rights reserved.
 * Commercial licence, see LICENSE.txt. https://magebooster.com
 * Licensed per serial number for one production installation (one store domain); staging and
 * development copies of that store are allowed. Serial numbers and downloads are tied to the customer.
 * You may modify this code for your own licensed store; keep this notice.
 * Not allowed: copying to other stores, redistributing, reselling, sublicensing, publishing it publicly,
 * removing licence checks or notices, sharing serial numbers. Unauthorised use: the serial number is
 * revoked without refund, updates and support stop, damages and legal action may follow.
 * Estonian law applies; court: Harju County Court (Harju Maakohus).
 */
declare(strict_types=1);

namespace MageBooster\License\Model;

/**
 * Paid modules that need a serial. Each paid module registers itself in its own di.xml:
 *
 * <type name="MageBooster\License\Model\Products"><arguments><argument name="products" xsi:type="array">
 *     <item name="moka" xsi:type="array">
 *         <item name="label" xsi:type="string">Moka United payments</item>
 *         <item name="codes" xsi:type="array">
 *             <item name="moka" xsi:type="string">moka</item>
 *             <item name="moka-hyva" xsi:type="string">moka-hyva</item>
 *         </item>
 *     </item>
 * </argument></arguments></type>
 *
 * A plain string item (code => label, the 1.0 format) is still accepted and stands for one code.
 */
class Products
{
    /**
     * @param array<string, string|array{label?: string, codes?: array<string, string>}> $products
     */
    public function __construct(private array $products = [])
    {
    }

    /**
     * @return array<string, string> module code => label
     */
    public function getAll(): array
    {
        $all = [];
        foreach ($this->products as $code => $item) {
            $all[(string)$code] = is_array($item) ? (string)($item['label'] ?? $code) : (string)$item;
        }
        return $all;
    }

    public function has(string $module): bool
    {
        return isset($this->products[$module]);
    }

    public function getLabel(string $module): string
    {
        return $this->getAll()[$module] ?? $module;
    }

    /**
     * Serial product codes accepted for a module: the module code first, then its editions (e.g. -hyva).
     *
     * @return string[]
     */
    public function getCodes(string $module): array
    {
        $codes = [$module];
        $item = $this->products[$module] ?? null;
        if (is_array($item) && isset($item['codes']) && is_array($item['codes'])) {
            foreach ($item['codes'] as $code) {
                $codes[] = (string)$code;
            }
        }
        return array_values(array_unique($codes));
    }
}
