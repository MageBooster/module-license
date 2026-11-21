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

namespace MageBooster\License\Model\System;

use MageBooster\License\Api\LicenseInterface;
use MageBooster\License\Model\ModuleState;
use MageBooster\License\Model\Products;
use Magento\Framework\Notification\MessageInterface;

/**
 * System message (top bar of every admin page) for paid MageBooster modules without a valid serial.
 *
 * It is shown during the grace period and after it, and goes away only when a valid serial is entered.
 * Magento offers no close or mark-as-read action for system messages. The text is plain (Magento escapes
 * it); Plugin\SystemMessagesLinks turns the settings path and the account address into links.
 */
class LicenseMessage implements MessageInterface
{
    public const ACCOUNT_URL = 'https://store.magebooster.com/licences/account/';

    /** @var ModuleState[]|null */
    private ?array $problems = null;

    public function __construct(
        private LicenseInterface $license,
        private Products $products
    ) {
    }

    public function getIdentity()
    {
        $keys = [];
        foreach ($this->getProblems() as $state) {
            $keys[] = $state->getModule() . ':' . ($state->isDisabled() ? 'off' : 'grace');
        }
        return 'magebooster_license_' . hash('sha256', implode('|', $keys));
    }

    public function isDisplayed()
    {
        return $this->getProblems() !== [];
    }

    public function getText()
    {
        $parts = [];
        foreach ($this->getProblems() as $state) {
            $parts[] = self::describe($state);
        }
        return (string)__('MageBooster licenses:') . ' ' . implode(' | ', $parts) . ' '
            . (string)__(
                'Enter the serial numbers in %1, or find them at %2',
                (string)__('Stores > Configuration > MageBooster > Licenses'),
                self::ACCOUNT_URL
            );
    }

    public function getSeverity()
    {
        return self::SEVERITY_CRITICAL;
    }

    /**
     * One line per module: name, reason, days left or the date it was switched off.
     */
    public static function describe(ModuleState $state): string
    {
        $text = $state->getLabel() . ': ' . $state->getStatus()->getMessage();
        if ($state->isValid()) {
            return $text;
        }
        if ($state->isInGrace()) {
            return $text . ' ' . (string)__('It keeps working for %1 more day(s).', $state->getDaysLeft());
        }
        $end = $state->getGraceEnd();
        return $text . ' ' . (string)__('Switched off since %1.', $end ? $end->format('Y-m-d') : '-');
    }

    /**
     * @return ModuleState[]
     */
    private function getProblems(): array
    {
        if ($this->problems === null) {
            $this->problems = [];
            foreach (array_keys($this->products->getAll()) as $module) {
                try {
                    $state = $this->license->getModuleState($module);
                } catch (\Throwable $e) {
                    continue;
                }
                if (!$state->isValid()) {
                    $this->problems[] = $state;
                }
            }
        }
        return $this->problems;
    }
}
