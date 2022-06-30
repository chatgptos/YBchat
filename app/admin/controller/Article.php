<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 文章管理
 *
 */
class Article extends Backend
{
    /**
     * Article模型对象
     * @var \app\admin\model\Article
     */
    protected $model = null;
    
	protected $quickSearchField = ['id'];

	protected $defaultSortField = 'article_id,desc';

	protected $preExcludeFields = [''];

    public function initialize()
    {
        parent::initialize();
        $this->model = new \app\admin\model\Article;
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