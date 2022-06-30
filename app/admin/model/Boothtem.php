<?php

namespace app\admin\model;

use think\Model;

/**
 * Boothtem
 * @controllerUrl 'boothtem'
 */
class Boothtem extends Model
{
    // 表名
    protected $name = 'booth_boothtem';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



}