<?php

namespace aogg\think\relation;

trait RelationShip {

    use \think\model\concern\RelationShip;

    /**
     * HAS MANY 远程关联定义
     *
     * $through.$foreignKey = parent.$localKey
     * $model.$throughKey = $through.$throughPk
     *
     * @access public
     * @param  string $model      需要的模型        主表模型，返回的数据用此model加载
     * @param  string $through    中间模型          关联模型
     * @param  string $foreignKey 中间模型          关联外键(关联当前模型的主键)
     * @param  string $throughKey 需要的模型        关联外键(关联中间模型的又一个外键)
     * @param  string $localKey   当前模型          主键
     * @param  string $throughPk  中间模型          又一个外键
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
     * HAS ONE 远程关联定义
     *
     * $through.$foreignKey = parent.$localKey
     * $model.$throughKey = $through.$throughPk
     *
     * @access public
     * @param  string $model      需要的模型        主表模型，返回的数据用此model加载
     * @param  string $through    中间模型          关联模型
     * @param  string $foreignKey 中间模型          关联外键(关联当前模型的主键)
     * @param  string $throughKey 需要的模型        关联外键(关联中间模型的又一个外键)
     * @param  string $localKey   当前模型          主键
     * @param  string $throughPk  中间模型          又一个外键
     * @return HasOneThrough
     */
    public function hasOneThroughLocal(string $model, string $through, string $foreignKey = '', string $throughKey = '', string $localKey = '', string $throughPk = ''): HasOneThrough
    {
        // 记录当前关联信息
        $model      = $this->parseModel($model);
        $through    = $this->parseModel($through);
        $localKey   = $localKey ?: $this->getPk();
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);
        $throughKey = $throughKey ?: $this->getForeignKey((new $through)->getName());
        $throughPk  = $throughPk ?: (new $through)->getPk();

        return new HasOneThrough($this, $model, $through, $foreignKey, $throughKey, $localKey, $throughPk);
    }

    /**
     * HAS ONE 关联定义
     *
     * @access public
     * @param  string $model      模型名
     * @param  string $foreignKey 关联外键         模型名的外键
     * @param  string $localKey   当前主键
     * @return HasOne
     */
    public function hasOneLocal(string $model, string $foreignKey = '', string $localKey = ''): HasOne
    {
        // 记录当前关联信息
        $model      = $this->parseModel($model);
        $localKey   = $localKey ?: $this->getPk();
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);

        return new HasOne($this, $model, $foreignKey, $localKey);
    }

    /**
     * HAS MANY 关联定义
     *
     * @access public
     * @param  string $model      模型名
     * @param  string $foreignKey 关联外键       模型名的外键
     * @param  string $localKey   当前主键
     * @return HasMany
     */
    public function hasManyLocal(string $model, string $foreignKey = '', string $localKey = ''): HasMany
    {
        // 记录当前关联信息
        $model      = $this->parseModel($model);
        $localKey   = $localKey ?: $this->getPk();
        $foreignKey = $foreignKey ?: $this->getForeignKey($this->name);

        return new HasMany($this, $model, $foreignKey, $localKey);
    }
}
