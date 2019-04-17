<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\privileges\migrations
 * @category   CategoryName
 */

use lispa\amos\core\migration\AmosMigrationPermissions;
use yii\rbac\Permission;

/**
 * Class m171218_113606_add_privileges_permission
 */
class m171218_113606_add_privileges_permission extends AmosMigrationPermissions
{
    /**
     * @inheritdoc
     */
    protected function setRBACConfigurations()
    {
        return [
            [
                'name' => 'PRIVILEGES_MANAGER',
                'type' => Permission::TYPE_ROLE,
                'description' => 'Manager role for privileges',
                'parent' => ['ADMIN']
            ]
        ];
    }
}
