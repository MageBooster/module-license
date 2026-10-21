<?php
declare(strict_types=1);

namespace MageBooster\License\Api;

use MageBooster\License\Model\Status;

/**
 * Serial-number check for a paid MageBooster product, e.g. isValid('currency-rates').
 */
interface LicenseInterface
{
    public function isValid(string $product): bool;

    public function getStatus(string $product): Status;
}
