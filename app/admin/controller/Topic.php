<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 峰会
 *
 */
class Topic extends Backend
{
    /**
     * Topic模型对象
     * @var \app\admin\model\Topic
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Topic;
    }

    public function add()
    {
        $this->request->filter('trim,htmlspecialchars');
        parent::add();
    }

    public function edit($id = null)
    {
        $this->request->filter('trim,htmlspecialchars');
        parent::edit($id);
    }
}