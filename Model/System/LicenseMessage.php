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
use MageBooster\License\Model\Products;
use MageBooster\License\Model\Status;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\UrlInterface;

/**
 * Admin notice listing paid MageBooster products whose serial is missing, invalid or about to lapse.
 */
class LicenseMessage implements MessageInterface
{
    private ?array $problems = null;

    public function __construct(
        private LicenseInterface $license,
        private Products $products,
        private UrlInterface $url
    ) {
    }

    public function getIdentity()
    {
        return 'magebooster_license_' . hash('sha256', implode('|', array_keys($this->getProblems())));
    }

    public function isDisplayed()
    {
        return $this->getProblems() !== [];
    }

    public function getText()
    {
        $lines = [];
        foreach ($this->getProblems() as $label => $status) {
            $lines[] = sprintf('<strong>%s</strong>: %s', htmlspecialchars($label, ENT_QUOTES | ENT_SUBSTITUTE), htmlspecialchars($status->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE));
        }
        return implode('<br>', $lines) . sprintf(
            '<br><a href="%s">%s</a>',
            $this->url->getUrl('adminhtml/system_config/edit', ['section' => 'magebooster_license']),
            __('Stores > Configuration > MageBooster > Licenses')
        );
    }

    public function getSeverity()
    {
        return self::SEVERITY_MAJOR;
    }

    /**
     * @return array<string, Status>
     */
    private function getProblems(): array
    {
        if ($this->problems === null) {
            $this->problems = [];
            foreach ($this->products->getAll() as $code => $label) {
                $status = $this->license->getStatus($code);
                if ($status->getCode() !== Status::VALID) {
                    $this->problems[$label] = $status;
                }
            }
        }
        return $this->problems;
    }
}
