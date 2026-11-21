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

use MageBooster\License\Api\LicenseInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class License implements LicenseInterface
{
    public const XML_PATH = 'magebooster_license/serials/';

    /** @var array<string, Status> */
    private array $cache = [];

    /** @var array<string, ModuleState> */
    private array $states = [];

    /** @var string[]|null */
    private ?array $hosts = null;

    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private StoreManagerInterface $storeManager,
        private Verifier $verifier,
        private Products $products,
        private Grace $grace
    ) {
    }

    public function isActive(string $module): bool
    {
        return $this->getModuleState($module)->isActive();
    }

    public function getModuleState(string $module): ModuleState
    {
        if (isset($this->states[$module])) {
            return $this->states[$module];
        }
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        try {
            $status = $this->bestStatus($this->products->getCodes($module));
        } catch (\Throwable $e) {
            $status = new Status(Status::INVALID, (string)__('The serial number could not be checked.'));
        }
        if ($status->getCode() === Status::VALID) {
            $this->grace->clear($module);
            $start = null;
        } else {
            $start = $this->grace->start($module, $now);
        }
        return $this->states[$module] = new ModuleState(
            $module,
            $this->products->getLabel($module),
            $status,
            $start,
            $start ? $this->grace->getEnd($start) : null,
            $now
        );
    }

    public function isValid(string $product): bool
    {
        return $this->isActive($product);
    }

    public function getStatus(string $product): Status
    {
        if (!isset($this->cache[$product])) {
            $serial = (string)$this->scopeConfig->getValue(self::XML_PATH . $this->configKey($product));
            $this->cache[$product] = $this->verifier->verify($serial, $product, $this->storeHosts());
        }
        return $this->cache[$product];
    }

    /**
     * VALID if any accepted code has a valid serial; otherwise the most telling problem
     * (an entered but rejected serial before a missing one, the module's own code first).
     *
     * @param string[] $codes
     */
    private function bestStatus(array $codes): Status
    {
        $best = null;
        foreach ($codes as $code) {
            $status = $this->getStatus($code);
            if ($status->getCode() === Status::VALID) {
                return $status;
            }
            if ($best === null || ($best->getCode() === Status::MISSING && $status->getCode() !== Status::MISSING)) {
                $best = $status;
            }
        }
        return $best ?? new Status(Status::MISSING, (string)__('No serial number has been entered.'));
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
        if ($this->hosts !== null) {
            return $this->hosts;
        }
        $hosts = [];
        foreach ($this->storeManager->getStores() as $store) {
            foreach ([false, true] as $secure) {
                $host = parse_url((string)$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, $secure), PHP_URL_HOST);
                if ($host) {
                    $hosts[$host] = $host;
                }
            }
        }
        return $this->hosts = array_values($hosts);
    }
}
