<?php

namespace app\admin\model;

use think\Model;

/**
 * Topic
 * @controllerUrl 'topic'
 */
class Topic extends Model
{
    // 表名
    protected $name = 'booth_topic';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;

	protected $type = [
		'start_time' => 'timestamp:Y-m-d H:i:s',
		'end_time'   => 'timestamp:Y-m-d H:i:s',
	];


    public function getActIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getHallIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getTopicContentAttr($value, $row)
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function getIntroAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getDataAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getCssAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getHtmlsAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}