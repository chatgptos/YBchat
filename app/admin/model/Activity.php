<?php

namespace app\admin\model;

use think\Model;

/**
 * Activity
 * @controllerUrl 'activity'
 */
class Activity extends Model
{
    // 表名
    protected $name = 'booth_activity';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



    public function getRegistrantIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}