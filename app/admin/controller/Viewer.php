<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 */
class Viewer extends Backend
{
    /**
     * Viewer模型对象
     * @var \app\admin\model\Viewer
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Viewer;
    }

}