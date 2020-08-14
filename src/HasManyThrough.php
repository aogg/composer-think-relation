<?php
/**
 * User: aogg
 * Date: 2020/8/8
 */

namespace aogg\think\relation;


use Closure;
use think\db\BaseQuery as Query;
use \think\helper\Str;

class HasManyThrough extends \think\model\relation\HasManyThrough
{
    /**
     * 定义model关联时候的on条件
     * 匿名函数需要返回字符串。参数固定（当前model表名，关联关系中第一个model表名，关联关系中第二个model表名），最后参数是query对象
     *
     * @var callable|string
     */
    protected $modelOn;

    protected $throughOn;

    /**
     * @var \think\db\Query
     */
    protected $query;

    /**
     * 当前model的别名
     *
     * @var string
     */
    protected $parentModelTableAlias;


    /**
     * 关联关系获取第一个model的数据
     *
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery($dataBool = false): void
    { // 未测试
        if (empty($this->baseQuery) && ($this->parent->getData() || $dataBool)) {
            $alias        = (new $this->model)->getTable(); // 关联关系中第一个model的表名，主表
            $throughTable = $this->through->getTable();
//            $pk           = $this->throughPk;
//            $throughKey   = $this->throughKey;
            $modelTable   = $this->parent->getTable(); // 当前model表面
            $fields       = $this->getQueryFields($alias);

            $this->query
                ->field($fields)
                ->alias($alias)
                ->join($throughTable, $this->getJoinOnString('through', $this->query, true))
                ->join($modelTable . ' ' . $this->getParentModelTableAlias(), $this->getJoinOnString('model', $this->query))
            ;

            $this->baseQuery = true;
        }
    }

    /**
     * 根据关联条件查询当前模型
     * 返回当前model的数据，主表是当前model
     *
     * @access public
     * @param  mixed  $where 查询条件（数组或者闭包）   关联关系中第一个model
     * @param  mixed  $fields 字段                    当前model
     * @param  string $joinType JOIN类型
     * @param  Query  $query    Query对象
     * @return Query|\think\db\Query
     */
    public function hasWhere($where = [], $fields = null, $joinType = '', Query $query = null, $groupByBool = true): Query
    {
        $model        = Str::snake(class_basename($this->parent));
        $this->setParentModelTableAlias($model);
        $throughTable = $this->through->getTable();
//        $pk           = $this->throughPk;
//        $throughKey   = $this->throughKey;
        $modelTable   = (new $this->model)->getTable();
        $whereBool = true;

        if (is_array($where)) {
            $this->getQueryWhere($where, $modelTable);
        } elseif ($where instanceof Query) {
            $where->via($modelTable);
        } elseif ($where instanceof Closure) {
            $where($this->query->via($modelTable));
            $where = $this->query;

            if (is_null($where->getOptions('where'))) { // 解决null进行foreach的问题
                $whereBool = false;
            }
        }

        $fields     = $this->getRelationQueryFields($fields, $model);
        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($model);
        /** @var \think\db\Query $query */

        return $query->join($throughTable, $this->getJoinOnString('through-hasWhere', $query), $joinType)
            ->join($modelTable, $this->getJoinOnString('model-hasWhere', $query, false), $joinType)
            ->when($softDelete, function ($query) use ($softDelete, $modelTable) {
                $query->where($modelTable . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
            })
            ->when($groupByBool, function ($query)use($modelTable){
                /** @var \think\db\Query $query */
                $query->group($modelTable . '.' . $this->throughKey);
            })
            ->when($whereBool, function ($query)use($where){
                /** @var \think\db\Query $query */
                $query->where($where);
            })
            ->field($fields);
    }

    /**
     * @return string
     */
    protected function getJoinOnString($type, $query, $valueBool = true)
    {
        $throughTable = $this->through->getTable();
        $parentTable   = $this->getParentModelTableAlias(); // 当前model表面
        $modelTable   = (new $this->model)->getTable(); // 关联关系中第一个model的表名，主表


        if (in_array($type, ['model', 'model-hasWhere'])) {
            $modelOn = $this->getModelOn();

            if (!empty($modelOn)) {
                if (is_string($modelOn)) {
                    $string = $modelOn;
                }else if(is_callable($modelOn)){
                    $string = call_user_func($modelOn, $parentTable, $modelTable, $throughTable, $query);
                }
            }

            if (empty($string) && $type === 'model-hasWhere'){
                goto throughJoin;
            }else if (empty($string)) {
                modelJoin:
                $string = $parentTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey;
            }
        }else if (in_array($type, ['through', 'through-hasWhere'])){
            $throughOn = $this->getThroughOn();

            if (!empty($throughOn)) {
                if (is_string($throughOn)) {
                    $string = $throughOn;
                }else if(is_callable($throughOn)){
                    $string = call_user_func($throughOn, $parentTable, $modelTable, $throughTable, $query);
                }
            }

            if (empty($string) && $type === 'through-hasWhere'){
                goto modelJoin;
            }else if (empty($string)) {
                throughJoin:
                $string = $throughTable . '.' . $this->throughPk . '=' . $modelTable . '.' . $this->throughKey;
                if ($valueBool) {
                    $string .= ' and ' . $throughTable . '.' . $this->foreignKey . '='. $this->parent->{$this->localKey};
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
}
