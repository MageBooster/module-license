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

namespace MageBooster\License\Api;

use MageBooster\License\Model\ModuleState;
use MageBooster\License\Model\Status;

/**
 * Licence check for paid MageBooster modules.
 *
 * Modules call isActive('<module code>'): true while a valid serial is entered, or for 30 days after
 * License first saw the module without one (grace period). After that the module switches its paid
 * function off in a safe way. The module code is the base code, e.g. 'moka'; the -hyva serial
 * ('moka-hyva') is accepted as well when the module registers it (see Model\Products).
 */
interface LicenseInterface
{
    /**
     * Valid serial or still within the grace period.
     */
    public function isActive(string $module): bool;

    /**
     * Full state (serial status, grace start and end) of a registered module.
     */
    public function getModuleState(string $module): ModuleState;

    /**
     * @deprecated 1.1.0 Use isActive(). Kept for modules built against 1.0; follows the same grace rules.
     */
    public function isValid(string $product): bool;

    /**
     * Serial check of one product code only (no grace period).
     */
    public function getStatus(string $product): Status;
}
