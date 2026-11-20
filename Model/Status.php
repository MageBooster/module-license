<?php
declare(strict_types=1);

namespace MageBooster\License\Model;

/**
 * Result of a serial check. VALID and GRACE both unlock the product; GRACE also shows a warning.
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
