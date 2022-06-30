<?php

namespace app\admin\model;

use think\Model;

/**
 * Ticket
 * @controllerUrl 'ticket'
 */
class Ticket extends Model
{
    // 表名
    protected $name = 'booth_ticket';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



    public function getUserIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getTopicIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getExhibitionIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}