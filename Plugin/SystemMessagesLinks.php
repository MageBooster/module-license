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

namespace MageBooster\License\Plugin;

use MageBooster\License\Model\System\LicenseMessage;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Magento prints system messages escaped, so links cannot be part of the message text.
 * This turns the two known plain-text parts of the licence message into links after rendering.
 */
class SystemMessagesLinks
{
    public function __construct(
        private UrlInterface $url,
        private Escaper $escaper
    ) {
    }

    /**
     * @param AbstractBlock $subject
     * @param string $html
     * @return string
     */
    public function afterToHtml(AbstractBlock $subject, $html)
    {
        if (!is_string($html) || strpos($html, LicenseMessage::ACCOUNT_URL) === false) {
            return $html;
        }
        return $this->linkify($html);
    }

    /**
     * Adds the two links to already escaped message HTML.
     */
    public function linkify(string $html): string
    {
        $label = $this->escaper->escapeHtml((string)__('Stores > Configuration > MageBooster > Licenses'));
        $configUrl = $this->url->getUrl('adminhtml/system_config/edit', ['section' => 'magebooster_license']);
        $html = str_replace(
            $label,
            '<a href="' . $this->escaper->escapeUrl($configUrl) . '">' . $label . '</a>',
            $html
        );
        return str_replace(
            LicenseMessage::ACCOUNT_URL,
            '<a href="' . LicenseMessage::ACCOUNT_URL . '" target="_blank" rel="noopener">'
                . LicenseMessage::ACCOUNT_URL . '</a>',
            $html
        );
    }
}
