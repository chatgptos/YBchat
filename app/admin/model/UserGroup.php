<?php

namespace app\admin\model;

use think\Model;

/**
 * UserGroup 模型
 * @controllerUrl 'userGroup'
 */
class UserGroup extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
}