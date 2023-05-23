<?php

/**
 * Aria S.p.A.
 * OPEN 2.0
 *
 *
 * @package    open20\amos\core\interfaces
 * @category   CategoryName
 */

namespace open20\amos\privileges\interfaces;

/**
 * Interface CategoriesRolesInterface
 * @package open20\amos\privileges\interfaces
 */
interface ServiceCategoriesRolesInterface
{
    /**
     * This method is the getter for model name to permissions.
     * 
     */

    public static function getServiceModelCategoryRole();
    public static function getServiceCategoryArrayRole();
    public static function getServiceCategoryArrayRoleAssignedToUser($userId);
}
