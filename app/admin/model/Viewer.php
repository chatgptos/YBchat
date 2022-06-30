<?php

namespace app\admin\model;

use think\Model;

/**
 * Viewer
 * @controllerUrl 'viewer'
 */
class Viewer extends Model
{
    // 表名
    protected $name = 'booth_viewer';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;

	protected $type = [
		'edit_time' => 'timestamp:Y-m-d H:i:s',
	];


    public function getRegistrantIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}