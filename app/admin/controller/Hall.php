<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展厅展区
 *
 */
class Hall extends Backend
{
    /**
     * Hall模型对象
     * @var \app\admin\model\Hall
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Hall;
    }

}