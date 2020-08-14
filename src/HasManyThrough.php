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
     * 关联关系获取第一个model的数据
     *
     * 执行基础查询（仅执行一次）
     * @access protected
     * @return void
     */
    protected function baseQuery($dataBool = false): void
    {
        if (empty($this->baseQuery) && ($this->parent->getData() || $dataBool)) {
            $alias        = (new $this->model)->getTable(); // 关联关系中第一个model的表名，主表
            $throughTable = $this->through->getTable();
            $pk           = $this->throughPk;
            $throughKey   = $this->throughKey;
            $modelTable   = $this->parent->getTable(); // 当前model表面
            $fields       = $this->getQueryFields($alias);

            $this->query
                ->field($fields)
                ->alias($alias)
                ->join($throughTable, $throughTable . '.' . $pk . '=' . $alias . '.' . $throughKey)
                ->join($modelTable, $modelTable . '.' . $this->localKey . '=' . $throughTable . '.' . $this->foreignKey)
                ->where($throughTable . '.' . $this->foreignKey, $this->parent->{$this->localKey});

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
        $throughTable = $this->through->getTable();
        $pk           = $this->throughPk;
        $throughKey   = $this->throughKey;
        $modelTable   = (new $this->model)->getTable();

        if (is_array($where)) {
            $this->getQueryWhere($where, $modelTable);
        } elseif ($where instanceof Query) {
            $where->via($modelTable);
        } elseif ($where instanceof Closure) {
            $where($this->query->via($modelTable));
            $where = $this->query;
        }

        $fields     = $this->getRelationQueryFields($fields, $model);
        $softDelete = $this->query->getOptions('soft_delete');
        $query      = $query ?: $this->parent->db()->alias($model);
        /** @var \think\db\Query $query */

        return $query->join($throughTable, $throughTable . '.' . $this->foreignKey . '=' . $model . '.' . $this->localKey)
            ->join($modelTable, $modelTable . '.' . $throughKey . '=' . $throughTable . '.' . $this->throughPk)
            ->when($softDelete, function ($query) use ($softDelete, $modelTable) {
                $query->where($modelTable . strstr($softDelete[0], '.'), '=' == $softDelete[1][0] ? $softDelete[1][1] : null);
            })
            ->when($groupByBool, function ($query)use($modelTable){
                /** @var \think\db\Query $query */
                $query->group($modelTable . '.' . $this->throughKey);
            })
            ->where($where)
            ->field($fields);
    }
}
