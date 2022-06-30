<?php

namespace app\admin\model;

use think\Model;

/**
 * Hall
 * @controllerUrl 'hall'
 */
class Hall extends Model
{
    // 表名
    protected $name = 'booth_hall';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



    public function getExhibitionIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}