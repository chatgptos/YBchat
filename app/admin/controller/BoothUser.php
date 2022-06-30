<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 
 *
 */
class BoothUser extends Backend
{
    /**
     * BoothUser模型对象
     * @var \app\admin\model\BoothUser
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\BoothUser;
    }

}