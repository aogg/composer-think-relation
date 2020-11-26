<?php
/**
 * User: aogg
 * Date: 2020/8/20
 */

namespace aogg\think\relation\traits;


use Closure;
use think\db\BaseQuery as Query;
use \think\helper\Str;

/**
 * @property-read \think\db\Query $query
 * @property-read \think\db\Query $through
 * @property-read \think\db\Query|\think\Model $parent
 * @method  \think\db\Query|\think\Model|\think\db\BaseQuery getQuery()
 */
trait HasManyThroughTrait
{
    /**
     * 自定义更多链表操作
     *
     * @var callable
     */
    protected $localQueryFunc;

    /**
     * @param mixed $localQueryFunc
     * @return HasManyThroughTrait
     */
    public function setLocalQueryFunc($localQueryFunc)
    {
        $this->localQueryFunc = $localQueryFunc;

        return $this;
    }

    /**
     * 关联关系获取第一个model的数据
     *
     * 执行基础查询（仅执行一次）
     * @param bool $dataBool
     * @access protected
     * @return void
     */
    protected function baseQuery($dataBool = false): void
    {
        if (empty($this->baseQuery) && (!empty($this->parent->{$this->localKey}) || $dataBool)) {
            $modelTable        = (new $this->model)->getTable(); // 关联关系中第一个model的表名，主表
            $throughTable = $this->through->getTable();
            $throughPk           = $this->throughPk;
            $throughKey   = $this->throughKey;
            $parentTable   = $this->getParentModelTableAlias(); // 当前model表面
            $fields       = $this->getQueryFields($modelTable);

            $this->query
                ->field($fields)
                ->alias($modelTable)
                ->join(
                    $throughTable, $throughTable . '.' . $throughPk . '=' . $modelTable . '.' . $throughKey .
                    $this->getJoinOnString('through', $this->getQuery())
                )
                ->join(
                    $parentTable, $parentTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey .

                    // 值的判断
                    ' and '. $throughTable . '.' . $this->foreignKey . '=' . $this->parent->{$this->localKey}
                )->where(1)
            ;
            $modelWhere = $this->joinOnWhere('model', $this->getQuery());

            is_callable($this->localQueryFunc) && call_user_func($this->localQueryFunc, $this->getQuery());

            $this->baseQuery = true;
        }
    }

    /**
     * 根据关联条件查询当前模型
     * 返回当前model的数据，主表是当前model
     *
     * @access public
     * @param mixed $where 查询条件（数组或者闭包）   关联关系中第一个model
     * @param mixed $fields 字段                    当前model
     * @param string $joinType JOIN类型
     * @param Query $query Query对象
     * @param bool $groupByBool 是否group
     * @return Query|\think\db\Query
     */
    public function hasWhere($where = [], $fields = null, $joinType = '', Query $query = null, $groupByBool = true): Query
    {
        /** @var string $parentModel */
        $parentTable  = $this->getParentModelTableAlias();
        $throughTable = $this->through->getTable();
        $throughPk           = $this->throughPk;
        $throughKey   = $this->throughKey;
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

        $fields     = $this->getRelationQueryFields($fields, $parentTable);
        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($parentTable);
        /** @var \think\db\Query $query */

        $query = $query->join(
            $throughTable, $throughTable . '.' . $this->foreignKey . '=' . $parentTable . '.' . $this->localKey .
            $this->getJoinOnString('through', $this->getQuery()),
            $joinType
        )
            ->join(
                $modelTable, $modelTable . '.' . $throughKey . '=' . $throughTable . '.' . $throughPk .
                $this->getJoinOnString('model', $this->getQuery()),
                $joinType
            )
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

        is_callable($this->localQueryFunc) && call_user_func($this->localQueryFunc, $query);

        return $query;
    }


}