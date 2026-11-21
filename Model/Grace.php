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

use Magento\Framework\FlagManager;

/**
 * Grace period start per module, stored in the flag table (flag code magebooster_license_grace).
 *
 * The start is written the first time a module is seen without a valid serial and removed when a
 * valid serial is found. It is never reset otherwise, so reinstalling or clearing caches does not
 * restart the period.
 */
class Grace
{
    public const FLAG = 'magebooster_license_grace';
    public const DAYS = 30;
    public const FORMAT = 'Y-m-d H:i:s';

    /** @var array<string, string>|null */
    private ?array $starts = null;

    public function __construct(private FlagManager $flagManager)
    {
    }

    public function getStart(string $module): ?\DateTimeImmutable
    {
        $value = $this->load()[$module] ?? null;
        if (!is_string($value)) {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat(self::FORMAT, $value, new \DateTimeZone('UTC'));
        return $date === false ? null : $date;
    }

    public function getEnd(\DateTimeImmutable $start): \DateTimeImmutable
    {
        return $start->modify('+' . self::DAYS . ' days');
    }

    /**
     * Start the grace period if it has not started yet; returns the start.
     */
    public function start(string $module, \DateTimeImmutable $now): \DateTimeImmutable
    {
        $start = $this->getStart($module);
        if ($start !== null) {
            return $start;
        }
        $starts = $this->load();
        $starts[$module] = $now->setTimezone(new \DateTimeZone('UTC'))->format(self::FORMAT);
        $this->save($starts);
        return $this->getStart($module) ?? $now;
    }

    public function clear(string $module): void
    {
        $starts = $this->load();
        if (array_key_exists($module, $starts)) {
            unset($starts[$module]);
            $this->save($starts);
        }
    }

    /**
     * @return array<string, string>
     */
    private function load(): array
    {
        if ($this->starts === null) {
            try {
                $data = $this->flagManager->getFlagData(self::FLAG);
            } catch (\Throwable $e) {
                $data = null;
            }
            $this->starts = is_array($data) ? $data : [];
        }
        return $this->starts;
    }

    /**
     * @param array<string, string> $starts
     */
    private function save(array $starts): void
    {
        $this->starts = $starts;
        try {
            $this->flagManager->saveFlag(self::FLAG, $starts);
        } catch (\Throwable $e) {
            // Never break a storefront request because the flag table is not writable; the start is kept for this request.
            return;
        }
    }
}
