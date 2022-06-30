<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 *  展位类别
 *
 */
class Boothtype extends Backend
{
    /**
     * Boothtype模型对象
     * @var \app\admin\model\Boothtype
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Boothtype;
    }

}