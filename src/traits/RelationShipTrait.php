<?php
/**
 * User: aogg
 * Date: 2020/8/22
 */

namespace aogg\think\relation\traits;


use aogg\think\relation\HasManyThrough;
use aogg\think\relation\HasOneThrough;
use think\Model;

trait RelationShipTrait
{

    /**
     * 判断远程关联关系的数据是否存在
     * 判断关联关系第一个model的数据
     *
     * @param string $relation 存在的关联关系的方法的名称
     * @param mixed $throughValue 关联键的值
     * @param null $moreWhere 更多where条件，可以数组或者匿名函数
     * @return bool
     */
    public function existsThroughRelationInModel($relation, $throughValue, $moreWhere = null)
    {
        $relation = \think\helper\Str::camel($relation);
        if (!method_exists($this, $relation)) { // 不存在的关联关系
            return false;
        }

        /** @var HasManyThrough|HasOneThrough|Model $relationObject */
        $relationObject = $this->{$relation}();

        $query = $relationObject->getModelNewQuery()->where($relationObject->getThroughKey(), $throughValue);

        if (isset($moreWhere)) {
            $query->where($moreWhere);
        }
        $query->field($relationObject->getThroughKey());

        /** @var object $query */
        return !!$query->find();
    }

    /**
     * 判断远程关联关系的数据是否存在
     * 判断中间表的数据
     *
     * @param string $relation 存在的关联关系的方法的名称
     * @param null $moreWhere 更多where条件，可以数组或者匿名函数
     * @return bool
     */
    public function existsThroughRelationInThrough($relation, $moreWhere = null)
    {
        $relation = \think\helper\Str::camel($relation);
        if (!method_exists($this, $relation)) { // 不存在的关联关系
            return false;
        }

        /** @var HasManyThrough|HasOneThrough|Model $relationObject */
        $relationObject = $this->{$relation}();

        $query = $relationObject->getThroughNewQuery()->where($relationObject->getOriginForeignKey(), $this->getData($relationObject->getLocalKey()));

        if (isset($moreWhere)) {
            $query->where($moreWhere);
        }
        $query->field($relationObject->getOriginForeignKey());

        /** @var object $query */
        return !!$query->find();
    }

    /**
     * 分离式  远程关联关系insert
     * 只保存through（中间表）的数据
     * 字段可以是key=>value结构
     *
     * @param array $relation
     * @return $this
     */
    public function togetherThroughInsertThrough(array $relation)
    {
        $relationWriteArr = $this->handleAutoRelationWrite($relation);

        foreach ($relationWriteArr as $name => $val) {

            $method = \think\helper\Str::camel($name);
            /** @var HasManyThrough|HasOneThrough|Model $relationObject */
            $relationObject = $this->$method();


            $foreignKey = $relationObject->getOriginForeignKey();
            $localKey = $relationObject->getLocalKey();
            if (!isset($val[$foreignKey]) && isset($this->$localKey)) { // 有关联字段就赋值
                $val[$foreignKey] = $this->$localKey;
            }


            $relationObject->getThroughNewQuery()->getModel()->create($val);
        }

        return $this;
    }


    /**
     * 关联数据自动写入检查
     *
     * @see checkAutoRelationWrite
     * @param $relation
     * @return array
     */
    protected function handleAutoRelationWrite($relation)
    {
        $result = [];

        foreach ($relation as $key => $name) {
            if (is_array($name)) {
                if (key($name) === 0) {
                    $result[$key] = [];
                    // 绑定关联属性
                    foreach ($name as $val) {
                        if (isset($this->$val)) { // 获取当前模型的数据
                            $result[$key][$val] = $this->$val;
                        }
                    }
                } else {
                    // 直接传入关联数据
                    $result[$key] = $name;
                }
            } elseif (isset($this->relation) && isset($this->relation[$name])) {
                $result[$name] = $this->relation[$name];
            } elseif (isset($this->$name)) {
                $result[$name] = $this->$name;

                // 立即处理，不连带
//                unset($this->data[$name]);
            }
        }

        return $result;
    }
}