<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展会活动管理
 *
 */
class Activity extends Backend
{
    /**
     * Activity模型对象
     * @var \app\admin\model\Activity
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Activity;
    }

}