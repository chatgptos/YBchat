<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展商
 *
 */
class Exhibitor extends Backend
{
    /**
     * Exhibitor模型对象
     * @var \app\admin\model\Exhibitor
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Exhibitor;
    }

}