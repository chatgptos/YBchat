<?php

namespace app\admin\model;

use think\Model;

/**
 * TestBuild
 * @controllerUrl 'testBuild'
 */
class TestBuild extends Model
{
    // 表名
    protected $name = 'test_build';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';


    protected static function onAfterInsert($model)
    {
        if ($model->weigh == 0) {
            $pk = $model->getPk();
            $model->where($pk, $model[$pk])->update(['weigh' => $model[$pk]]);
        }
    }

    public function getKeywordRowsAttr($value, $row)
    {
        return !$value ? '' : $value;
    }

    public function getContentAttr($value, $row)
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function getNoteTextareaAttr($value, $row)
    {
        return !$value ? '' : $value;
    }
}