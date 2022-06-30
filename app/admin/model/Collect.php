<?php

namespace app\admin\model;

use think\Model;

/**
 * Collect
 * @controllerUrl 'collect'
 */
class Collect extends Model
{
    // 表名
    protected $name = 'booth_user_collect';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;

	protected $type = [
		'add_time' => 'timestamp:Y-m-d H:i:s',
	];


    public function getUserIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getExhibitorIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getActivityIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getGoodsIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getTopicIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}