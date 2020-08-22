<?php
/**
 * User: aogg
 * Date: 2020/8/19
 */

namespace aogg\think\relation;

use \think\helper\Str;

class HasOneThrough extends \think\model\relation\HasOneThrough
{
    use traits\ThroughRelationHelperTrait,
        traits\HasManyThroughTrait;

}