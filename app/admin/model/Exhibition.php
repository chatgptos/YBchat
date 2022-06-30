<?php

namespace app\admin\model;

use think\Model;

/**
 * Exhibition
 * @controllerUrl 'exhibition'
 */
class Exhibition extends Model
{
    // 表名
    protected $name = 'booth_exhibition';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



}