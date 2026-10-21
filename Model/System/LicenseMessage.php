<?php
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
        private readonly LicenseInterface $license,
        private readonly Products $products,
        private readonly UrlInterface $url
    ) {
    }

    public function getIdentity()
    {
        return 'magebooster_license_' . md5(implode('|', array_keys($this->getProblems())));
    }

    public function isDisplayed()
    {
        return $this->getProblems() !== [];
    }

    public function getText()
    {
        $lines = [];
        foreach ($this->getProblems() as $label => $status) {
            $lines[] = sprintf('<strong>%s</strong>: %s', htmlspecialchars($label), htmlspecialchars($status->getMessage()));
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
