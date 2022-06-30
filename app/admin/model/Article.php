<?php

namespace app\admin\model;

use think\Model;

/**
 * Article
 * @controllerUrl 'article'
 */
class Article extends Model
{
    // 表名
    protected $name = 'booth_article';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;

	protected $type = [
		'add_time' => 'timestamp:Y-m-d H:i:s',
	];


    public function getCatIdAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getContentAttr($value, $row)
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}