<?php

namespace app\admin\model;

use think\Model;

/**
 * BoothUser
 * @controllerUrl 'boothUser'
 */
class BoothUser extends Model
{
    // 表名
    protected $name = 'booth_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



    public function getBoothIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getExhibitorIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}