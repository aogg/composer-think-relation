<?php
/**
 * User: aogg
 * Date: 2020/8/8
 */

namespace aogg\think\relation;



class HasManyThrough extends \think\model\relation\HasManyThrough
{
    use traits\ThroughRelationHelperTrait,
        traits\HasManyThroughTrait,
        traits\ThroughRelationActionTrait;


}