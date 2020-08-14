<?php

namespace aogg\think\relation;

trait RelationShip {

    use \think\model\concern\RelationShip;

    /**
     * HAS MANY 远程关联定义
     * @access public
     * @param  string $model      需要的模型名    主表模型，返回的数据用此model转载
     * @param  string $through    中间模型名      关联模型
     * @param  string $foreignKey 中间模型名 的 关联外键
     * @param  string $throughKey 需要的模型名 的 关联外键
     * @param  string $localKey   当前模型 的 主键
     * @param  string $throughPk  中间表主键
     * @return HasManyThrough
     */
    public function hasManyThroughLocal(string $model, string $through, string $foreignKey = '', string $throughKey = '', string $localKey = '', string $throughPk = ''): HasManyThrough
    {
        // 记录当前关联信息
        $model      = $this->parseModel($model);
        $through    = $this->parseModel($through);
        $localKey   = $localKey ?: $this->getPk();
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);
        $throughKey = $throughKey ?: $this->getForeignKey((new $through)->getName());
        $throughPk  = $throughPk ?: (new $through)->getPk();

        return new HasManyThrough($this, $model, $through, $foreignKey, $throughKey, $localKey, $throughPk);
    }

    /**
     * HAS MANY 远程关联定义
     * 支持join的on条件
     *
     * @param string $model
     * @param string $throughModel
     * @param $modelOn
     * @param $throughOn
     * @param string $localKey
     * @return HasManyThrough
     */
    public function hasManyThroughOnFncLocal(string $model, string $throughModel, $modelOn, $throughOn, string $localKey = '')
    {
        $localKey   = $localKey ?: $this->getPk();

        return (new HasManyThrough($this, $model, $throughModel, '', '', $localKey, ''))
            ->setModelOn($modelOn)
            ->setThroughOn($throughOn)
            ;
    }
}
