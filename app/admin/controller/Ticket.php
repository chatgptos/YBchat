<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 电子门票
 *
 */
class Ticket extends Backend
{
    /**
     * Ticket模型对象
     * @var \app\admin\model\Ticket
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Ticket;
    }

}