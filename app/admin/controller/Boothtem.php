<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展位模板管理
 *
 */
class Boothtem extends Backend
{
    /**
     * Boothtem模型对象
     * @var \app\admin\model\Boothtem
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Boothtem;
    }

}