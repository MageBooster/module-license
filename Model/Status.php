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
 * Result of a serial check of one product code. Since 1.1.0 the verifier no longer returns GRACE;
 * the grace period is handled per module by License::isActive().
 */
class Status
{
    public const VALID = 'valid';
    public const GRACE = 'grace';
    public const MISSING = 'missing';
    public const INVALID = 'invalid';
    public const EXPIRED = 'expired';
    public const WRONG_PRODUCT = 'wrong_product';
    public const WRONG_DOMAIN = 'wrong_domain';

    public function __construct(
        private string $code,
        private string $message,
        private ?string $expires = null
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getExpires(): ?string
    {
        return $this->expires;
    }

    public function isUsable(): bool
    {
        return $this->code === self::VALID || $this->code === self::GRACE;
    }
}
