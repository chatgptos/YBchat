<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展会维护管理
 *
 */
class Exhibition extends Backend
{
    /**
     * Exhibition模型对象
     * @var \app\admin\model\Exhibition
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Exhibition;
    }

}