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

namespace MageBooster\License\Console\Command;

use MageBooster\License\Api\LicenseInterface;
use MageBooster\License\Model\Products;
use MageBooster\License\Model\System\LicenseMessage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * bin/magento magebooster:license:status - licence state of every installed paid MageBooster module.
 */
class StatusCommand extends Command
{
    public function __construct(
        private LicenseInterface $license,
        private Products $products
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('magebooster:license:status')
            ->setDescription('Shows the licence state of the installed paid MageBooster modules');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (array_keys($this->products->getAll()) as $module) {
            $state = $this->license->getModuleState($module);
            $phase = $state->isValid() ? 'active' : ($state->isInGrace() ? 'grace' : 'off');
            $output->writeln(sprintf('%-16s %-6s %s', $module, $phase, LicenseMessage::describe($state)));
        }
        return 0;
    }
}
