<?php

namespace app\admin\model;

use think\Model;

/**
 * SensitiveData 模型
 * @controllerUrl 'securitySensitiveData'
 */
class SensitiveData extends Model
{
    protected $name = 'security_sensitive_data';

    protected $autoWriteTimestamp = 'int';
    protected $createTime         = 'createtime';
    protected $updateTime         = 'updatetime';

    protected $type = [
        'data_fields' => 'array',
    ];
}