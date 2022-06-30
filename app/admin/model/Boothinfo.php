<?php

namespace app\admin\model;

use think\Model;

/**
 * Boothinfo
 * @controllerUrl 'boothinfo'
 */
class Boothinfo extends Model
{
    // 表名
    protected $name = 'booth_boothinfo';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



    public function getExhibitionIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getHallIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getBoothtemIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getBoothtypeIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}