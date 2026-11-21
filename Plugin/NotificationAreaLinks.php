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

use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\Framework\Escaper;

/**
 * The admin header (notification_area grid) prints the message text as HTML.
 * The licence message is escaped here and gets its two links, like in the legacy popup.
 */
class NotificationAreaLinks
{
    public function __construct(
        private SystemMessagesLinks $links,
        private Escaper $escaper
    ) {
    }

    /**
     * @param DataProvider $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        if (!is_array($result) || !isset($result['items']) || !is_array($result['items'])) {
            return $result;
        }
        foreach ($result['items'] as $key => $item) {
            if (is_array($item) && isset($item['text']) && is_string($item['text'])
                && str_starts_with((string)($item['identity'] ?? ''), 'magebooster_license')
            ) {
                $result['items'][$key]['text'] = $this->links->linkify((string)$this->escaper->escapeHtml($item['text']));
            }
        }
        return $result;
    }
}
