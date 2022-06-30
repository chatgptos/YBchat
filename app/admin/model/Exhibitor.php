<?php

namespace app\admin\model;

use think\Model;

/**
 * Exhibitor
 * @controllerUrl 'exhibitor'
 */
class Exhibitor extends Model
{
    // 表名
    protected $name = 'booth_user_exhibitor';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;

	protected $type = [
		'edit_time'     => 'timestamp:Y-m-d H:i:s',
		'approved_time' => 'timestamp:Y-m-d H:i:s',
	];


    public function getExhibitorIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getZhIntroduceAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getEnIntroduceAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}