<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 知识库管理
 *
 */
class TestBuild extends Backend
{
    /**
     * TestBuild模型对象
     * @var \app\admin\model\TestBuild
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'weigh,desc';

	protected $preExcludeFields = ['createtime', 'updatetime'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\TestBuild;
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