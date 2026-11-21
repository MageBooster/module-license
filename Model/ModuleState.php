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
 * Licence state of one module: serial status plus grace period.
 */
class ModuleState
{
    public function __construct(
        private string $module,
        private string $label,
        private Status $status,
        private ?\DateTimeImmutable $graceStart,
        private ?\DateTimeImmutable $graceEnd,
        private \DateTimeImmutable $now
    ) {
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Serial status of the best matching serial (VALID when one of the module's serials verifies).
     */
    public function getStatus(): Status
    {
        return $this->status;
    }

    public function isValid(): bool
    {
        return $this->status->getCode() === Status::VALID;
    }

    /**
     * Valid serial, or no valid serial but still within the grace period.
     */
    public function isActive(): bool
    {
        return $this->isValid() || ($this->graceEnd !== null && $this->now < $this->graceEnd);
    }

    public function isInGrace(): bool
    {
        return !$this->isValid() && $this->isActive();
    }

    public function isDisabled(): bool
    {
        return !$this->isActive();
    }

    public function getGraceStart(): ?\DateTimeImmutable
    {
        return $this->graceStart;
    }

    public function getGraceEnd(): ?\DateTimeImmutable
    {
        return $this->graceEnd;
    }

    /**
     * Whole days left in the grace period, rounded up (1 on the last day), 0 when not in grace.
     */
    public function getDaysLeft(): int
    {
        if (!$this->isInGrace() || $this->graceEnd === null) {
            return 0;
        }
        return max(1, (int)ceil(($this->graceEnd->getTimestamp() - $this->now->getTimestamp()) / 86400));
    }
}
