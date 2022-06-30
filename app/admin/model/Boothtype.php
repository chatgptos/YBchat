<?php

namespace app\admin\model;

use think\Model;

/**
 * Boothtype
 * @controllerUrl 'boothtype'
 */
class Boothtype extends Model
{
    // 表名
    protected $name = 'booth_boothtype';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    protected $createTime = false;
    protected $updateTime = false;



}