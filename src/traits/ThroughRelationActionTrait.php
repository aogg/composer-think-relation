<?php
/**
 * User: aogg
 * Date: 2020/8/24
 */

namespace aogg\think\relation\traits;

/**
 * @method  \think\db\BaseQuery|\think\db\Query getModelNewQuery()
 * @method  \think\db\BaseQuery|\think\db\Query getThroughNewQuery()
 * @method  \think\Model getParent()
 */
trait ThroughRelationActionTrait
{

    /**
     * 判断远程关联关系的数据是否存在
     * 判断关联关系第一个model的数据
     *
     * @param mixed $throughValue 关联键的值
     * @param null $moreWhere 更多where条件，可以数组或者匿名函数
     * @return bool
     */
    public function existsInModel($throughValue, $moreWhere = null)
    {

        $relationObject = $this;

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
     * @param null $moreWhere 更多where条件，可以数组或者匿名函数
     * @return bool
     */
    public function existsInThrough($moreWhere = null)
    {
        $relationObject = $this;

        $query = $relationObject->getThroughNewQuery()->where($relationObject->getOriginForeignKey(), $this->getParent()->{$relationObject->getLocalKey()});

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
     * @param array $save
     * @return $this
     */
    public function insertThrough($save)
    {
        $save = $this->handleAutoRelationWrite($save);


        $relationObject = $this;


        $foreignKey = $relationObject->getOriginForeignKey();
        $localKey = $relationObject->getLocalKey();
        if (!isset($save[$foreignKey]) && isset($this->getParent()->$localKey)) { // 有关联字段就赋值
            $save[$foreignKey] = $this->getParent()->$localKey;
        }


        $relationObject->getThroughNewQuery()->getModel()->create($save);

        return $this;
    }


    /**
     * 关联数据自动写入检查
     *
     * @param $save
     * @return array
     * @see checkAutoRelationWrite
     */
    protected function handleAutoRelationWrite($save)
    {
        $result = [];


        if (is_array($save)) {
            if (key($save) === 0) {
                $result = [];
                // 绑定关联属性
                foreach ($save as $val) {
                    if (isset($this->getParent()->$val)) { // 获取当前模型的数据
                        $result[$val] = $this->getParent()->$val;
                    }
                }
            } else {
                // 直接传入关联数据
                $result = $save;
            }
        } elseif (isset($this->getParent()->relation) && isset($this->getParent()->relation[$save])) {
            $result[$save] = $this->getParent()->relation[$save];
        } elseif (isset($this->getParent()->$save)) {
            $result[$save] = $this->getParent()->$save;

            // 立即处理，不连带
//                unset($this->data[$name]);
        }

        return $result;
    }
}