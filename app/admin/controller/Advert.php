<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 广告管理
 *
 */
class Advert extends Backend
{
    /**
     * Advert模型对象
     * @var \app\admin\model\Advert
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'weigh,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Advert;
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