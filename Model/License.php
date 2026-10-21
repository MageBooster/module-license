<?php
declare(strict_types=1);

namespace MageBooster\License\Model;

use MageBooster\License\Api\LicenseInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class License implements LicenseInterface
{
    public const XML_PATH = 'magebooster_license/serials/';

    /** @var array<string, Status> */
    private array $cache = [];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private readonly Verifier $verifier
    ) {
    }

    public function isValid(string $product): bool
    {
        return $this->getStatus($product)->isUsable();
    }

    public function getStatus(string $product): Status
    {
        if (!isset($this->cache[$product])) {
            $serial = (string)$this->scopeConfig->getValue(self::XML_PATH . $this->configKey($product));
            $this->cache[$product] = $this->verifier->verify($serial, $product, $this->storeHosts());
        }
        return $this->cache[$product];
    }

    private function configKey(string $product): string
    {
        return str_replace('-', '_', $product);
    }

    /**
     * Hosts of every store view's base URLs (the admin URL is not checked).
     *
     * @return string[]
     */
    private function storeHosts(): array
    {
        $hosts = [];
        foreach ($this->storeManager->getStores() as $store) {
            foreach ([false, true] as $secure) {
                $host = parse_url((string)$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, $secure), PHP_URL_HOST);
                if ($host) {
                    $hosts[$host] = $host;
                }
            }
        }
        return array_values($hosts);
    }
}
