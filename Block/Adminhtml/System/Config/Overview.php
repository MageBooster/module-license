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

namespace MageBooster\License\Block\Adminhtml\System\Config;

use MageBooster\License\Api\LicenseInterface;
use MageBooster\License\Model\Products;
use MageBooster\License\Model\System\LicenseMessage;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Licence state of every installed paid MageBooster module, shown above the serial fields.
 */
class Overview extends Field
{
    public function __construct(
        Context $context,
        private LicenseInterface $license,
        private Products $products,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element)
    {
        $rows = '';
        foreach (array_keys($this->products->getAll()) as $module) {
            try {
                $state = $this->license->getModuleState($module);
            } catch (\Throwable $e) {
                continue;
            }
            if ($state->isValid()) {
                $label = (string)__('Active');
            } elseif ($state->isInGrace()) {
                $label = (string)__('Grace period');
            } else {
                $label = (string)__('Switched off');
            }
            $rows .= '<tr><td><strong>' . $this->escapeHtml($state->getLabel()) . '</strong></td><td>'
                . $this->escapeHtml($label) . '</td><td>'
                . $this->escapeHtml(LicenseMessage::describe($state)) . '</td></tr>';
        }
        if ($rows === '') {
            $rows = '<tr><td colspan="3">' . $this->escapeHtml((string)__('No paid MageBooster module is installed.')) . '</td></tr>';
        }
        return '<tr id="row_' . $this->escapeHtmlAttr($element->getHtmlId()) . '"><td colspan="4">'
            . '<table class="data-grid"><thead><tr><th class="data-grid-th">' . $this->escapeHtml((string)__('Module'))
            . '</th><th class="data-grid-th">' . $this->escapeHtml((string)__('Status'))
            . '</th><th class="data-grid-th">' . $this->escapeHtml((string)__('Details')) . '</th></tr></thead><tbody>'
            . $rows . '</tbody></table><p class="note"><span>'
            . $this->escapeHtml((string)__('Your serial numbers:')) . ' <a href="' . LicenseMessage::ACCOUNT_URL
            . '" target="_blank" rel="noopener">' . LicenseMessage::ACCOUNT_URL . '</a></span></p></td></tr>';
    }
}
