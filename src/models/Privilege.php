<?php

/**
 * Lombardia Informatica S.p.A.
 * OPEN 2.0
 *
 *
 * @package    lispa\amos\privileges
 * @category   CategoryName
 */

namespace lispa\amos\privileges\models;

use yii\base\Model;

/**
 * Class Privilege
 * @package lispa\amos\privileges\models
 */
class Privilege extends Model
{

    public $name;
    public $type;
    public $text;
    public $description;
    public $tooltip;
    public $domains;
    public $active = false;
    public $can = false;
    public $isChild;
    public $parents = [];
    public $isCwh = false;
    public $isPlatformUserClass = false;

}