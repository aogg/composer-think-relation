<?php
/**
 * User: aogg
 * Date: 2020/8/19
 */

namespace aogg\think\relation\traits;

use think\Model;

/**
 * @property-read \think\db\Query $query
 * @property-read \think\db\Query $through
 * @property-read \think\db\Query|Model $parent
 * @method  \think\db\Query|Model|\think\db\BaseQuery getQuery()
 */
trait ThroughRelationHelperTrait
{
    /**
     * 定义model关联时候的on条件
     * 匿名函数需要返回字符串，需要自己添加and。参数固定（当前model表名，关联关系中第一个model表名，关联关系中第二个model表名），最后参数是query对象
     *
     * @var callable|string
     */
    protected $modelOn;

    protected $throughOn;


    /**
     * 当前model的别名
     *
     * @var string
     */
    protected $parentModelTableAlias;


    public function getLocalKey()
    {
        return $this->localKey;
    }

    public function getOriginForeignKey()
    {
        return $this->foreignKey;
    }

    /**
     * @return string
     */
    public function getThroughKey()
    {
        return $this->throughKey;
    }

    public function getThroughPk()
    {
        return $this->throughPk;
    }

    public function getThroughNewQuery()
    {
        return $this->through->getModel()->newQuery();
    }

    public function getModelNewQuery()
    {
        return $this->getQuery()->getModel()->newQuery();
    }

    /**
     * @param $type
     * @param $query
     * @return string
     */
    protected function getJoinOnString($type, $query)
    {
        $throughTable = $this->through->getTable();
        $parentTable   = $this->getParentModelTableAlias(); // 当前model表面
        $modelTable   = (new $this->model)->getTable(); // 关联关系中第一个model的表名，主表

        $string = '';
        if (in_array($type, ['model'])) {
            $modelOn = $this->getModelOn();

            if (!empty($modelOn)) {
                if (is_string($modelOn)) {
                    $string = $modelOn;
                }else if(is_callable($modelOn)){
                    $string = call_user_func($modelOn, $parentTable, $modelTable, $throughTable, $query);
                }
            }

        }else if (in_array($type, ['through'])){
            $throughOn = $this->getThroughOn();

            if (!empty($throughOn)) {
                if (is_string($throughOn)) {
                    $string = $throughOn;
                }else if(is_callable($throughOn)){
                    $string = call_user_func($throughOn, $parentTable, $modelTable, $throughTable, $query);
                }
            }

        }

        return $string;
    }

    /**
     * @return mixed
     */
    public function getThroughOn()
    {
        return $this->throughOn;
    }

    /**
     * @param mixed $throughOn
     * @return $this
     */
    public function setThroughOn($throughOn)
    {
        $this->throughOn = $throughOn;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getModelOn()
    {
        return $this->modelOn;
    }

    /**
     * @param mixed $modelOn
     * @return $this
     */
    public function setModelOn($modelOn)
    {
        $this->modelOn = $modelOn;

        return $this;
    }


    public function getParentModelTableAlias()
    {
        return $this->parentModelTableAlias?:$this->parent->getTable();
    }

    public function setParentModelTableAlias($parentModelTableAlias)
    {
        $this->parentModelTableAlias = $parentModelTableAlias;

        return $this;
    }

    /**
     * 返回model的类名
     *
     * @return mixed
     */
    public function getModelString()
    {
        return $this->model;
    }

}