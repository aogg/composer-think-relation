<?php
/**
 * User: aogg
 * Date: 2020/9/24
 */

namespace aogg\think\relation;


class HasMany extends \think\model\relation\HasMany
{
    use \aogg\think\relation\traits\HasRelationHelperTrait;

    /**
     * 检测列表是否包含某个值
     *
     * @param string $field 检测的字段
     * @param string|int $value 检测的值
     * @param bool $strict 是否全等于判断
     * @return bool
     * @throws \Exception
     */
    public function existsValue($field, $value, $strict = false)
    {
        $query = $this->getQuery();

        if (strpos($field, '.') !== false) {
            $fieldArr = explode('.', $field);
            $fieldTrue = !empty($fieldArr[0])?$fieldArr[0]:'';
            unset($fieldArr[0]);
        }else{
            $fieldArr = [$field];
            $fieldTrue = $field;
        }

        if (empty($fieldTrue)) {
            throw new \Exception('field错误: ' . $field);
        }

        if (isset($this->parent->{$this->localKey})) {
            // 关联查询带入关联条件
            $query->where($this->foreignKey, '=', $this->parent->{$this->localKey});
        }
        $list = $query->field($fieldTrue)->select()->column($fieldTrue);

        foreach ($list as $item) {
            foreach ($fieldArr as $fieldItem) {
                if (is_object($item)) {
                    if (!isset($item->$fieldItem)) {
                        continue 2;
                    }

                    $item = $item->$fieldItem;
                }else if (is_array($item)){
                    if (!isset($item[$fieldItem])) {
                        continue 2;
                    }

                    $item = $item[$fieldItem];
                }

                if ($strict && $item === $value){
                    return true;
                }else if (!$strict && $item == $value){
                    return true;
                }
            }
        }

        return false;
    }
}
