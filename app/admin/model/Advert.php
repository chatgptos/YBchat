<?php

namespace app\admin\model;

use think\Model;

/**
 * Advert
 * @controllerUrl 'advert'
 */
class Advert extends Model
{
    // 表名
    protected $name = 'booth_advert';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;


    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
        }
    }

    public function getAdminIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getCategoryIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getContentAttr($value, $row)
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function getCityAttr($value, $row)
    {
        if ($value == '') {
            return [];
        }
        if (!is_array($value)) {
            return explode(',', $value);
        }
        return $value;
    }

    public function setCityAttr($value, $row)
    {
        if ($value && is_array($value)) {
            return implode(',', $value);
        }
        return $value ? $value : '';
    }
}