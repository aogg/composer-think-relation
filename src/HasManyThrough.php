<?php
/**
 * User: aogg
 * Date: 2020/8/8
 */

namespace aogg\think\relation;


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
            $alias        = (new $this->model)->getTable(); // 第一个model的表名，主表
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
}
