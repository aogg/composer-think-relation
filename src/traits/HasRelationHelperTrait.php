<?php
/**
 * User: aogg
 * Date: 2020/9/24
 */

namespace aogg\think\relation\traits;

/**
 * @mixin \aogg\think\relation\HasOne
 */
trait HasRelationHelperTrait
{
    /**
     * 不存在时才创建数据
     * 将where数据转为create数据
     *
     * @param array $save
     * @return \think\Model|null
     */
    public function createOnNotExistsWhere($save = [])
    {
        $query = $this->getQuery();

        $options = $query->getOptions('where');

        /** @var object $query */
        if ($data = $query->find()) {
            return $data;
        }

        if (!empty($options['AND'])) { // 解析where的=转为存储的数据
            foreach ($options['AND'] as $option) {
                if (empty($option[0]) || isset($save[$option[0]])) {
                    continue;
                }

                if (!empty($option[1]) && $option[1] === '=') {
                    $save[$option[0]] = isset($option[2])?$option[2]:null;
                }
            }
        }

        /** @var \think\db\Query $query */
        return $query->getModel()->create($save);
    }

    /**
     * 创建数据
     * 将where数据转为create数据
     *
     * @param array $save
     * @return \think\Model|null
     */
    public function createOnWhere($save = [])
    {
        $query = $this->getQuery();

        $options = $query->getOptions('where');

        if (!empty($options['AND'])) { // 解析where的=转为存储的数据
            foreach ($options['AND'] as $option) {
                if (empty($option[0]) || isset($save[$option[0]])) {
                    continue;
                }

                if (!empty($option[1]) && $option[1] === '=') {
                    $save[$option[0]] = isset($option[2])?$option[2]:null;
                }
            }
        }

        return $query->getModel()->create($save);
    }
}