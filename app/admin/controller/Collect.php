<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 收藏
 *
 */
class Collect extends Backend
{
    /**
     * Collect模型对象
     * @var \app\admin\model\Collect
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Collect;
    }

}