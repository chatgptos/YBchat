<?php
// 2020年2月18日18:06:58
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use fast\Tree;
use think\Db;

/**
 * flbooth页面接口
 */
class Page extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
    /**
     * 获取APP首页
     *
     * @ApiSummary  (flbooth 获取自定义页面数据)
     * @ApiMethod   (GET)
     *
     * @param string $id 页面ID
     */
    public function index($id = null)
    {
		$error = __('页面不存在');
		$row = !$id ? $this->error($error) : model('app\api\model\flbooth\Page')
			->where(['page_token' => $id])
			->field('page, item')
			->find();
		!$row ? $this->error($error) : $this->success('返回成功', $row);
    }
	/**
	 * 获取指定文章
	 *
	 * @ApiSummary  (flbooth 产品接口获取文章)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function article($ids = null)
	{
	    $row = model('app\api\model\flbooth\Article')
	    	->where('id', 'in', $ids)
	    	->field('id,title,image,views,created')
	    	->select();
	    $this->success('ok', $row);
	}
	
	/**
	 * 获取头条文章
	 *
	 * @ApiSummary  (flbooth 产品接口获取文章)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function headlines()
	{
		$config = get_addon_config('flbooth');
	    $row = model('app\api\model\flbooth\Article')
	    	->where([
				['EXP', Db::raw("FIND_IN_SET('index', `flag`)")],
				'category_id' => $config['config']['new_category']
			])
	    	->field('id,title,image')
	    	->limit(20)
	    	->select();
	    $this->success('ok', $row);
	}
	
	/**
	 * 获取商品
	 *
	 * @ApiSummary  (flbooth 产品接口获取商品)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function goods($ids = null)
	{
		$list = model('app\api\model\flbooth\Goods')
			->where('id', 'in' ,$ids)
			->field('id,image,title,price,shop_id,comment,praise')
			->select();
		foreach($list as $row){
			$row->shop->visible(['state','shopname']);
			$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $row['shop_id'], 'state' => 1])->field('id')->find();
		}
		$this->success('ok', $list);
	}
	
	
	
	/**
	 * 获取热门营销活动-拼团
	 *
	 * @ApiSummary  (flbooth 页面接口获取热门营销活动-拼团品)
	 * @ApiMethod   (GET)
	 * 火线上线，后续通过算法查询
	 * 
	 */
	public function groups($shop_id = NULL)
	{
		$where = [];
		if($shop_id){
			$where['shop_id'] = $shop_id;
		}
		$list = model('app\api\model\flbooth\groups\Goods')
			->orderRaw('rand()')
			->where($where)
			->field('id,image,title,description,price')
			->limit(6)
			->select();
		$this->success('ok', $list);
	}
	
	/**
	 * 获取活动橱窗
	 *
	 * @ApiSummary  (flbooth 获取活动橱窗)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function activity()
	{
		$param = $this->request->param();
		// 数据样式
		$col = [];
		switch ($param['col'])
		{
			case "col-1-2-2":
				$col = [4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-1-1_2":
				$col = [4,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-1_2":
				$col = [2,2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2_1":
				$col = [2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2-1_2":
				$col = [2,2,2,2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-4":
				$col = [2,2,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2-4":
				$col = [2,2,2,2,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
		}
		$activity = [
			'distribution' => '分销',
			'group' => '团购营销活动-拼团',
			'bargain' => '砍价',
			'rush' => '限时抢购',
			'coupon' => '领券中心'
		];
		$list = [];
		foreach(json_decode(html_entity_decode($param['data']),true) as $key => $data){
			if($key < 9){
				$row = [];
				switch ($data['activity'])
				{
					case "group":
					$row = model('app\api\model\flbooth\groups\Goods')
						->orderRaw('rand()')
						->limit($col[$key])
						->field('id,image')
						->select();
					break;
				}
				$list[] = [
					'activity' => $data['activity'],
					'activityText' => $activity[$data['activity']],
					'color' => $data['textColor'],
					'describe' => $data['describe'],
					'tags' => $data['tags'],
					'list' => $row
				];
			}
		}
		$this->success('ok', $list);
	}
	
	/**
	 * 获取类目商品
	 *
	 * @ApiSummary  (flbooth 页面接口获取类目商品)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function category()
	{
		$param = $this->request->param();
		// 数据样式
		$col = [];
		switch ($param['col'])
		{
			case "col-1-2-2":
				$col = [4,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-1-1_2":
				$col = [4,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-1_2":
				$col = [2,2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2_1":
				$col = [2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2-1_2":
				$col = [2,2,2,2,2,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-4":
				$col = [2,2,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
			case "col-2-2-4":
				$col = [2,2,2,2,1,1,1,1,2,2,2,2,2,2,2,2,2,2,2,2];
				break;
		}
		// 查询数据
		$list = [];
		foreach(json_decode(html_entity_decode($param['data']),true) as $key => $data){
			if($key < 20){
				$category = Tree::instance()->init(model('app\api\model\flbooth\Category')->all())->getChildren($data['categoryId'], true);
				$category_ids = array_column($category, 'id');
				$row = model('app\api\model\flbooth\Goods')
					->where('category_id', 'in', $category_ids)
					->orderRaw('rand()')
					->limit($col[$key])
					->field('id,image')
					->select();
				$list[] = [
					'name' => $category[0]['name'],
					'color' => $data['textColor'],
					'describe' => $data['describe'],
					'tags' => $data['tags'],
					'list' => $row
				];
			}
		}
		$this->success('ok', $list);
	}
	
	
	
}
