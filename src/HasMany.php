<?php
/**
 * User: aogg
 * Date: 2020/9/24
 */

namespace aogg\think\relation;


class HasMany extends \think\model\relation\HasMany
{
    use \aogg\think\relation\traits\HasRelationHelperTrait;
}