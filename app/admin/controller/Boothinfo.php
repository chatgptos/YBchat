<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 展位信息
 *
 */
class Boothinfo extends Backend
{
    /**
     * Boothinfo模型对象
     * @var \app\admin\model\Boothinfo
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Boothinfo;
    }

}