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

namespace MageBooster\License\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * 1.3.0 moved the shared "MageBooster" admin parent from MageBooster_Reports::magebooster to
 * MageBooster_License::magebooster. Each role keeps the allow or deny it had on the old parent, so it keeps
 * seeing (or not seeing) the MageBooster menu and its entries after the upgrade.
 */
class MoveMenuParentRule implements DataPatchInterface
{
    private const OLD = 'MageBooster_Reports::magebooster';
    private const NEW = 'MageBooster_License::magebooster';

    public function __construct(private ResourceConnection $resource)
    {
    }

    public function apply(): self
    {
        $db = $this->resource->getConnection();
        $table = $this->resource->getTableName('authorization_rule');
        $roles = array_unique($db->fetchCol($db->select()->from($table, 'role_id')->where('resource_id = ?', self::OLD)));
        foreach ($roles as $roleId) {
            // Rename the old row instead of adding a new one. The ACL applies rules in rule_id order and a rule on a
            // parent also sets its children, so the parent row has to keep its place before the children's rows
            // (a role saved in the admin writes them in tree order); an appended row would undo the children's deny.
            $db->delete($table, ['role_id = ?' => (int) $roleId, 'resource_id = ?' => self::NEW]);
            $db->update($table, ['resource_id' => self::NEW], ['role_id = ?' => (int) $roleId, 'resource_id = ?' => self::OLD]);
        }
        return $this;
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
