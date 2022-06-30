<?php

namespace app\api\controller\flbooth;

use addons\flbooth\library\WanlSdk\Common;
use app\common\controller\Api;
use fast\Tree;
use think\Db;

/**
 * flbooth产品接口
 */
class Product extends Api
{
	protected $noNeedLogin = ['lists', 'goods', 'drawer', 'comment', 'likes', 'stock'];
	protected $noNeedRight = ['*'];
    
	protected $excludeFields = "";
	
    /**
     * 获取商品列表
     *
     * @ApiSummary  (flbooth 获取商品列表)
     * @ApiMethod   (GET)
	 * 
	 */
    public function lists($type = 'goods')
    {
    	//设置过滤方法
    	$this->request->filter(['strip_tags']);
		// 判断业务类型
		if($type === 'goods'){
			$goodsModel  = model('app\api\model\flbooth\Goods');
		}else if($type === 'groups'){
			$goodsModel  = model('app\api\model\flbooth\groups\Goods');
		}
    	// 生成搜索条件
    	list($where, $sort, $order) = $this->buildparams('title,category.name',false); // 查询标题 和类目字段  ！！！！！！排除已下架//-------------------------------------------
		// 查询数据
    	$list = $goodsModel
    		->with(['shop','category'])
    	    ->where($where)
			->where('goods.status', 'normal')
    	    ->order($sort, $order)
    	    ->paginate();
    	foreach ($list as $row) {
    	    $row->getRelation('shop')->visible(['city', 'shopname', 'state', 'isself']);
    		$row->getRelation('category')->visible(['id','pid','name']);
    		$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $row['shop_id'], 'state' => 1])->field('id')->find();
    	}	
    	$this->success('返回成功', $list);
    }
    
	/**
	 * 搜索获取品牌列表
	 *
	 * @ApiSummary  (flbooth 获取品牌列表)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function drawer($type = 'goods')
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$search = $this->request->request("search"); // 查询商品品牌
		$id = $this->request->request("category_id"); // 查询类目品牌
		// 判断业务类型
		if($type === 'goods'){
			$goodsModel  = model('app\api\model\flbooth\Goods');
		}else if($type === 'groups'){
			$goodsModel  = model('app\api\model\flbooth\groups\Goods');
		}
		$brandModel = model('app\api\model\flbooth\Brand');
		$attributeModel  = model('app\api\model\flbooth\Attribute');
		
		// 1.0.8升级  获取父级类目属性
		$category_id = null;
		$attribute_ids = null;
		$tree = Tree::instance();
		$tree->init(collection(model('app\index\model\flbooth\Category')->select())->toArray(), 'pid');
		
		// 直接查询类目
		if($id){
			$category_id = $id;
			$attribute_ids = $tree->getParentsIds($id, true);
		}
		// 通过商品类目查询
		if($search){
			$ids = [];
			foreach ($goodsModel->where('title', 'like', '%'.$search.'%')->select() as $row) {
				$ids[] = $row['category_id'];
			}
			$ids = array_flip($ids);
			$category_ids = '';
			foreach ($ids as $key => $value){
			    $category_ids .= implode(',', $tree->getParentsIds($key, true)).',';
			}
			$category_id = array_keys($ids);
			$attribute_ids = array_keys(array_flip(explode(',', rtrim($category_ids, ','))));
		}
		// 返回数据
		$this->success('返回成功', [
			'brand' => $brandModel
				->where('category_id', 'in', $category_id)
				->where('status','normal')
				->field('name')
				->select(), 
			'attribute' => $attributeModel
				->where('category_id', 'in', $attribute_ids)
				->where('status','normal')
				->field('name,value')
				->select()
		]);
	}
	
	/**
	 * 猜你喜欢
	 *
	 * @ApiSummary  (flbooth 猜你喜欢)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $pages 页面ID
	 * @param string $category_id 类目ID
	 */
	public function likes()
	{
		$pages = $this->request->request('pages'); //不同页面不同排序,goods只获得与当前产品相同类目,index获得排名靠前的,user随意获取
		$category_id = $this->request->request('cid');
		// 判断来源
		if($pages == 'index'){
			$sort = 'payment';
		}else if($pages == 'user'){
			$sort = 'comment';
		}else if($pages == 'cart'){
			$sort = 'views';
		}else if($pages == 'goods'){
			$sort = 'weigh';
		}else{
			$sort = 'like';
		}
		$uuid = $this->request->server('HTTP_UUID');
		if(!isset($uuid)){
			$charid = strtoupper(md5($this->request->header('user-agent').$this->request->ip()));
			$uuid = substr($charid, 0, 8).chr(45).substr($charid, 8, 4).chr(45).substr($charid,12, 4).chr(45).substr($charid,16, 4).chr(45).substr($charid,20,12);
		}
		// 统计
		$record = model('app\api\model\flbooth\Record')->where(['uuid'=>$uuid]);
		// 获取上架商品 1.0.3升级
		$where['status'] = 'normal'; 
		//如果没有
		if($record->count() == 0){
			if($category_id){
				$category_pid = model('app\api\model\flbooth\Category')->get($category_id);
				$array = model('app\api\model\flbooth\Category')
					->where(['pid' => $category_pid['pid']])
					->select();
				$cid = [];
				foreach ($array as $value) {
					$cid[] = $value['id'];
				}
				$where['category_id'] = ['in',$cid];
			}
			$goods = model('app\api\model\flbooth\Goods')
				->where($where)
				->orderRaw('rand()')
				->field('id,shop_id,title,image,flag,price,views,sales,comment,praise,like')
				->paginate();
		}else{
			$like_cat = array_count_values($record->column('category_pid')); //喜欢的类目
			$like_goods_cat = array($record->order('views', 'desc')->find()['category_pid']); //喜欢产品的类目
			arsort($like_cat); //排序
			$like_cat_top = array_slice(array_keys($like_cat),0,5); //排名前5
			$category_pid = array_intersect($like_cat_top,$like_goods_cat); //是否包含喜欢的产品类目
			// 如果包含输入产品类目,如果不包含输出排名第一的
			if($category_pid){
				$category_pid = array_slice($category_pid,0,1)[0];
			}else{
				$category_pid = $like_cat_top[0];
			}
			// 查询指定
			if($category_id){
				$category_pid = model('app\api\model\flbooth\Category')->get($category_id)['pid'];
			}
			//查询下级类目
			$array = model('app\api\model\flbooth\Category')
				->where(['pid' => $category_pid])
				->select();
			$cid = [];
			foreach ($array as $value) {
				$cid[] = $value['id'];
			}
			$where['category_id'] = ['in',$cid];
			// 查询父ID下所有商品
			$goods = model('app\api\model\flbooth\Goods')
				->where($where)
				->orderRaw('rand()')
				->field('id,shop_id,title,image,flag,price,views,sales,comment,praise,like')
				->paginate();
		}
		foreach ($goods as $row) {
			$row->shop->visible(['state','shopname']);
			$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $row['shop_id'], 'state' => 1])->field('id')->find();
		}
		$this->success('返回成功', $goods);
	}
	
    /**
     * 获取商品详情
     *
     * @ApiSummary  (flbooth  访问记录)
     * @ApiMethod   (GET)
     * 
     * @param string $id 商品ID
     */
    public function goods()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->request("id"); 
		// 是否传入商品ID
		$id ? $id : ($this->error(__('非正常访问')));
		// 加载商品模型
		$goodsModel = model('app\api\model\flbooth\Goods');
		// 查询商品
		$goods = $goodsModel
			->where(['id' => $id])
			->field('id,category_id,shop_category_id,brand_id,freight_id,shop_id,title,image,images,flag,content,category_attribute,activity_type,price,sales,payment,comment,praise,moderate,negative,like,views,status')
			->find();
		// 浏览+1 & 报错
		if($goods && $goods['status'] == 'normal'){
			// 查询类目
			$goods->category->visible(['id','pid','name']);
			// 查询优惠券
			$goods['coupon'] = $this->queryCoupon($goods['id'], $goods['shop_id'], $goods['shop_category_id'], $goods['price']);
			// 查询是否关注
			$goods['follow'] = $this->isfollow($id);
			// 查询品牌
			$goods->brand->visible(['name']);
			// 查询SKU
			$goods['sku'] = $goods->sku;
			// 查询SPU
			$goods['spu'] = $goods->spu;
			// 查询直播状态
			$goods['isLive'] = model('app\api\model\flbooth\Live')->where(['shop_id' => $goods['shop_id'], 'state' => 1])->field('id')->find();
			// 查询评论
			$goods['comment_list'] = $goods->comment_list;
			// 获取店铺详情
			$goods->shop->visible(['shopname','service_ids','avatar','city','like','score_describe','score_service','score_logistics']);
			// 查询快递 运费ID 商品重量 邮递城市 商品数量
			$goods['freight'] = $this->freight($goods['freight_id']);
			// 查询促销
			$goods['promotion'] = $id; //--下个版本更新--
			// 店铺推荐
			$goods['shop_recommend'] = $goodsModel
				->where(['shop_id' => $goods['shop_id'], 'status' => 'normal']) //还可以使用 , 'flag' => 'recommend'
				->field('id,title,image,price')
				->limit(3)
				->select();
			// 浏览+1
			$goods->setInc('views'); 
			// 写入访问日志
			$this->addbrowse($goods); 
			// 返回结果
			$this->success('返回成功', $goods);
		}else{
			$this->error(__('对不起当前商品不存在或已下架！'));
		}
    }
	
	/**
	 * 实时查询库存
	 *
	 * @ApiSummary  (flbooth 实时查询库存)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $sku_id  SKU
	 */
	public function stock($sku_id = '')
	{
		$redis = Common::redis();
		$sku = model('app\api\model\flbooth\GoodsSku')->get($sku_id);
		$sku_key = 'goods_'.$sku['goods_id'].'_'.$sku['id'];
		// 获取缓存数量
		$llen = $redis->llen("{$sku_key}");
		if(!$llen){
			for ($i = 0; $i < $sku['stock']; $i ++) {
				$redis->lpush("{$sku_key}", 1);
			}
		}
		$this->success('查询成功', $redis->llen("{$sku_key}"));
	}
	
	/**
	 * 是否关注商品
	 *
	 * @ApiSummary  (flbooth 保存浏览记录)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $goods  商品数据
	 */
	public function isfollow($goods_id ='')
	{
		$data = false;
		if ($this->auth->isLogin()) {
			$follow = model('app\api\model\flbooth\GoodsFollow')
				->where([
					'user_id' => $this->auth->id, 
					'goods_id' => $goods_id,
					'goods_type' => 'goods'
				])
				->count();
			$data = $follow == 0 ? false : true; //关注
		}
		return $data;
	}
	
	/**
	 * 保存浏览记录
	 *
	 * @ApiSummary  (flbooth 保存浏览记录)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $goods  商品数据
	 */
	public function addbrowse($goods =[])
	{
		//保存浏览记录
		$uuid = $this->request->server('HTTP_UUID');
		if(!isset($uuid)){
			$charid = strtoupper(md5($this->request->header('user-agent').$this->request->ip()));
			$uuid = substr($charid, 0, 8).chr(45).substr($charid, 8, 4).chr(45).substr($charid,12, 4).chr(45).substr($charid,16, 4).chr(45).substr($charid,20,12);
		}
		$recordModel = model('app\api\model\flbooth\Record');
		$goods_type = 'goods';
		$record = $recordModel
			->where([
				'uuid' => $uuid,
				'goods_id' => $goods['id'],
				'goods_type' => $goods_type
			])
			->find();
		if($record){
			$update['id'] = $record['id'];
			if ($this->auth->isLogin()) {
				$update['user_id'] = $this->auth->id;
			}
			$update['views'] = $record['views'] + 1;
			$record->update($update);
		}else{
			if ($this->auth->isLogin()) {
				$recordModel->user_id = $this->auth->id;
			}
			$recordModel->uuid = $uuid;
			$recordModel->goods_id = $goods['id'];
			$recordModel->goods_type = $goods_type;
			$recordModel->shop_id = $goods['shop_id'];
			$recordModel->category_id = $goods['category']['id'];
			$recordModel->category_pid = $goods['category']['pid'];
			$recordModel->ip = $this->request->ip();
			$recordModel->save();
		}
	}
	
	/**
	 * 关注商品
	 *
	 * @ApiSummary  (flbooth 关注或取消商品)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 商品ID
	 */
	public function follow()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$id = $this->request->post("id");
			// 是否传入商品ID
			$id ? $id : ($this->error(__('非正常访问')));
			// 加载商品模型
			$goodsModel = model('app\api\model\flbooth\Goods');
			$goodsFollowModel = model('app\api\model\flbooth\GoodsFollow');
			$data = [
				'user_id' => $this->auth->id, 
				'goods_id' => $id,
				'goods_type' => 'goods'
			];
			if($goodsFollowModel->where($data)->count() == 0){
				$goodsFollowModel->save($data);
				$goodsModel->where(['id' => $id])->setInc('like'); //喜欢+1
				$follow = true;
			}else{
				$goodsFollowModel->where($data)->delete();
				$goodsModel->where(['id' => $id])->setDec('like'); //喜欢-1
				$follow = false;
			}
			$this->success('返回成功', $follow);
		}
		$this->error(__('非正常访问'));
	}
	
	/**
	 * 收藏夹列表
	 */
	public function collect($type = 'goods')
	{
		$followIds = [];
		$followModel = model('app\api\model\flbooth\GoodsFollow');
		// 判断业务类型
		if($type === 'goods'){
			$goodsModel = model('app\api\model\flbooth\Goods');
			$field = 'id, shop_id, title, image, views, price, sales, payment, like';
		}else if($type === 'groups'){
			$goodsModel = model('app\api\model\flbooth\groups\Goods');
			$field = 'id, shop_id, title, image, views, price, sales, payment, like, is_ladder, people_num';
		}
		// 获取收藏夹IDS
		foreach ($followModel->where(['user_id' => $this->auth->id, 'goods_type' => $type])->select() as $row) {
			// 排除不存在商品
		    if($goodsModel->get($row['goods_id'])){
				$followIds[] = $row['id'];
			}
		}	
		// 按条件查询
		$list = $followModel
			->where('id', 'in', $followIds)
			->field('goods_id')
			->paginate();
		foreach ($list as $row) {
		    $row['goods'] = $goodsModel
				->where(['id' => $row['goods_id']])
				->field($field)
				->find();
		}
		$this->success('返回成功', $list);
	}
	
	/**
	 * 足迹列表
	 */
	public function footprint($type = 'goods')
	{
		$footprintIds = [];
		$recordModel = model('app\api\model\flbooth\Record');
		// 判断业务类型
		if($type === 'goods'){
			$goodsModel = model('app\api\model\flbooth\Goods');
		}else if($type === 'groups'){
			$goodsModel = model('app\api\model\flbooth\groups\Goods');
		}
		// 1.0.8升级  通过uuid查询足迹
		$uuid = $this->request->server('HTTP_UUID');
		if(!isset($uuid)){
			$charid = strtoupper(md5($this->request->header('user-agent').$this->request->ip()));
			$uuid = substr($charid, 0, 8).chr(45).substr($charid, 8, 4).chr(45).substr($charid,12, 4).chr(45).substr($charid,16, 4).chr(45).substr($charid,20,12);
		}
		// 获取足迹IDS
		$record = $recordModel->where(['uuid' => $uuid, 'goods_type' => $type])->select();
		foreach ($record as $row) {
			// 排除不存在商品
		    if($goodsModel->get($row['goods_id'])){
				$footprintIds[] = $row['id'];
			}
		}	
		// 按条件查询
		$list = $recordModel
			->where('id', 'in', $footprintIds)
			->field('goods_id, created')
			->order('created', 'desc')
			->paginate();
		foreach ($list as $row) {
		    $row['goods'] = $goodsModel
				->where(['id' => $row['goods_id']])
				->field('id, image, title, price, payment')
				->find();
		}
		$this->success('返回成功', $list);
	}
	
	/**
	 * 查询用户指定店铺浏览记录 
	 *
	 * @ApiSummary  (查询用户指定店铺浏览记录 1.0.2升级)
	 * @ApiMethod   (POST)
	 *
	 * @param string $shop_id 店铺ID
	 */
	public function getBrowsingToShop()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$shop_id = $this->request->post('shop_id');
			$shop_id ? '':($this->error(__('Invalid parameters')));
			$list = model('app\api\model\flbooth\Record')
				->where(['shop_id' => $shop_id, 'user_id' => $this->auth->id])
				->group('goods_id')
				->field('goods_id, created')
				->select();
			foreach ($list as $row) {
				// 1.0.8升级
				$row->goods ? $row->goods->visible(['id', 'image', 'title', 'price']) : false;
			}
			$this->success(__('发送成功'), $list);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 获取商品评论
	 *
	 * @ApiSummary  (flbooth 获取商品下所有评论)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $tag 评论分类
	 * @param string $id  商品ID
	 * @param string $list_rows  每页数量
	 * @param string $page  当前页
	 */
	public function comment()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->request("id"); 
		$tag = $this->request->request('tag');
		// 是否传入商品ID
		$id ? $id : ($this->error(__('非正常访问')));
		// 加载商品模型
		$goodsCommentModel = model('app\api\model\flbooth\GoodsComment')->order('created desc');
		//查询tag 评价:0=好评,1=中评,2=差评
		if($tag){
			if($tag == 'good'){
				$where['state'] = 0;
			}else if($tag == 'pertinent'){
				$where['state'] = 1;
			}else if($tag == 'poor'){
				$where['state'] = 2;
			}else if($tag == 'figure'){
				$where['images'] = ['neq', ''];//有图
			}else{
				$where['tag'] = $tag;
			}
		}
		$where['goods_id'] = $id;
		$where['order_type'] = 'goods';
		$comment['comment'] = $goodsCommentModel
			->with(['user'])
			->where($where)
			->paginate();
		// $comment['tag'] = array_count_values($goodsCommentModel->where(['goods_id'=>$id])->limit(100)->column('tag')); //统计热词
		foreach ($comment['comment'] as $row) {
			$row->getRelation('user')->visible(['username','nickname','avatar']);
		}
		$goods = model('app\api\model\flbooth\Goods')
			->where(['id' => $id])
			->find();
		$comment['statistics'] = [
			'rate'     => $goods['comment'] == 0 ? '0' : bcmul(bcdiv($goods['praise'], $goods['comment'], 2), 100, 2),
			'total'    => $goods['comment'], 
			'good'     => $goods['praise'],
			'pertinent'=> $goods['moderate'],     
			'poor'     => $goods['negative'],
			'figure'   => $goodsCommentModel->where(['goods_id' => $id])->where('images','neq', '')->count()
		];
		$this->success('返回成功', $comment);
	}
	
	/**
	 * 获取运费模板和子类
	 * @param string $id  运费ID
	 * @param string $weigh  商品重量
	 * @param string $city  邮递城市
	 * @param string $number  商品数量
	 */
	private function freight($id = null, $weigh = 0, $city = '北京', $number = 1)
	{
		// 运费模板
		$data = model('app\api\model\flbooth\ShopFreight')->where('id', $id)->field('id,delivery,isdelivery,name,valuation')->find();
		$data['price'] = 0;
		// 是否包邮:0=自定义运费,1=卖家包邮
		if($data['isdelivery'] == 0){
			// 获取地址编码 1.1.0升级
			$list = model('app\api\model\flbooth\ShopFreightData')
				->where([
					['EXP', Db::raw('FIND_IN_SET('.model('app\common\model\Area')->get(['name' => $city])->id.', citys)')],
					'freight_id' => $id
				])
				->find();
			// 查询是否存在运费模板数据
			if(!$list){
				$list = model('app\api\model\flbooth\ShopFreightData')->get(['freight_id' => $id]);
			}
			
			// 计价方式:0=按件数,1=按重量,2=按体积
			if($data['valuation'] == 0){
				if($number <= $list['first']){
					$price = $list['first_fee'];
				}else{
					$price = ceil(($number - $list['first']) / $list['additional']) * $list['additional_fee'] + $list['first_fee'];
				}
			}else{
				$weigh = $weigh * $number; // 订单总重量
				if($weigh <= $list['first']){ // 如果重量小于等首重，则首重价格
					$price = $list['first_fee'];
				}else{
					$price = ceil(($weigh - $list['first']) / $list['additional']) * $list['additional_fee'] + $list['first_fee'];
				}
			}
			$data['price'] = $price;
		}
		return $data;
	}
	
	/**
	 * 查询我的优惠券 
	 *
	 * @param string $goods_id 商品ID
	 * @param string $shop_id 店铺ID
	 * @param string $shop_category_id 分类ID
	 * @param string $price 价格 
	 */
	private function queryCoupon($goods_id = null, $shop_id = null, $shop_category_id = null, $price = null)
	{
		$user_coupon = [];
		if ($this->auth->isLogin()) {
			foreach (model('app\api\model\flbooth\CouponReceive')->where([
				'user_id' => $this->auth->id, 
				'shop_id' => $shop_id,
				'limit' => ['<=', intval($price)],
				'state' => '1'
			])->select() as $row) {
				$user_coupon[$row['coupon_id']] = $row;
			}
		}
		// 开始查询
		$list = [];
		$goods_id = explode(",",$goods_id);
		$shop_category_id = explode(",",$shop_category_id);
		//要追加一个排序 选出一个性价比最高的
		foreach (model('app\api\model\flbooth\Coupon')->where([
			'shop_id' => $shop_id,
			'limit' => ['<=', intval($price)]
		])->select() as $row) { 
			// 筛选出还未开始的
			if(!($row['pretype'] == 'fixed' && (strtotime($row['startdate']) >= time() || strtotime($row['enddate']) < time()))){
				//追加字段
				$row['choice'] = false;
				// 检查指定的键名是否存在于数组中
				if(array_key_exists($row['id'], $user_coupon)){
					$row['invalid'] = 0; // 强制转换优惠券状态
					$row['id'] = $user_coupon[$row['id']]['id'];
					$row['state'] = true;
				}else{
					$row['state'] = false;
				}
				// 排除失效优惠券
				if($row['invalid'] == 0){
					// 高级查询，比较数组，返回交集如果和原数据数目相同则加入
					if($row['rangetype'] == 'all'){
						$list[] = $row;
					}
					if($row['rangetype'] == 'goods' && count($goods_id) == count(array_intersect($goods_id, explode(",",$row['range'])))){
						$list[] = $row;
					}
					// 1.1.0升级
					if($row['rangetype'] == 'category'){
						// 判断优化全类目是否在商品类目中，explode(',', $row['range'])[0] 目的向前兼容
						if( in_array(explode(',', $row['range'])[0], $shop_category_id) ){
							$list[] = $row;
						}
					}
				}
			}
		}
		return $list;
	}
	
	
	
	/**
	 * 生成查询所需要的条件,排序方式
	 * @param mixed   $searchfields   快速查询的字段
	 * @param boolean $relationSearch 是否关联查询
	 * @return array
	 */
	protected function buildparams($searchfields = null, $relationSearch = null)
	{
	    $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
	    $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
		// 获取传参
	    $search = $this->request->get("search", '');
	    $filter = $this->request->get("filter", '');
	    $op = $this->request->get("op", '', 'trim');
	    $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
	    $order = $this->request->get("order", "DESC");
	    $filter = (array)json_decode($filter, true);
	    $op = (array)json_decode($op, true);
	    $filter = $filter ? $filter : [];
	    $where = [];
	    $tableName = '';
	    if ($relationSearch) {
	        if (!empty($this->model)) {
	            $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
	            $name = $this->model->getTable();
	            $tableName = $name . '.';
	        }
	        $sortArr = explode(',', $sort);
	        foreach ($sortArr as $index => & $item) {
	            $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
	        }
	        unset($item);
	        $sort = implode(',', $sortArr);
	    }
		
		
		// 判断是否需要验证权限
		// if (!$this->auth->match($this->noNeedLogin)) {
		//     $where[] = [$tableName . 'user_id', 'in', $this->auth->id];
		// }
		
	    if ($search) {
	        $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
	        foreach ($searcharr as $k => &$v) {
	            $v = stripos($v, ".") === false ? $tableName . $v : $v;
	        }
	        unset($v);
	        $arrSearch = [];
	        foreach (explode(" ", $search) as $ko) {
	        	$arrSearch[] = '%'.$ko.'%';
	        }
	        $where[] = [implode("|", $searcharr), "LIKE", $arrSearch];
	    }
		// 历遍所有
		if (array_key_exists('category_id', $filter)) {
			$filter['category_id'] = implode(',', array_column(Tree::instance()->init(model('app\api\model\flbooth\Category')->all())->getChildren($filter['category_id'], true), 'id'));
		}
	    foreach ($filter as $k => $v) {
	        $sym = isset($op[$k]) ? $op[$k] : '=';
	        if (stripos($k, ".") === false) {
	            $k = $tableName . $k;
	        }
	        $v = !is_array($v) ? trim($v) : $v;
	        $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
	        switch ($sym) {
	            case '=':
	            case '<>':
	                $where[] = [$k, $sym, (string)$v];
	                break;
	            case 'LIKE':
	            case 'NOT LIKE':
	            case 'LIKE %...%':
	            case 'NOT LIKE %...%':
	                $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
	                break;
	            case '>':
	            case '>=':
	            case '<':
	            case '<=':
	                $where[] = [$k, $sym, intval($v)];
	                break;
	            case 'FINDIN':
	            case 'FINDINSET':
	            case 'FIND_IN_SET':
	                $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
	                break;
	            case 'IN':
	            case 'IN(...)':
	            case 'NOT IN':
	            case 'NOT IN(...)':
	                $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
	                break;
	            case 'BETWEEN':
	            case 'NOT BETWEEN':
	                $arr = array_slice(explode(',', $v), 0, 2);
	                if (stripos($v, ',') === false || !array_filter($arr)) {
	                    continue 2;
	                }
	                //当出现一边为空时改变操作符
	                if ($arr[0] === '') {
	                    $sym = $sym == 'BETWEEN' ? '<=' : '>';
	                    $arr = $arr[1];
	                } elseif ($arr[1] === '') {
	                    $sym = $sym == 'BETWEEN' ? '>=' : '<';
	                    $arr = $arr[0];
	                }
	                $where[] = [$k, $sym, $arr];
	                break;
	            case 'RANGE':
	            case 'NOT RANGE':
	                $v = str_replace(' - ', ',', $v);
	                $arr = array_slice(explode(',', $v), 0, 2);
	                if (stripos($v, ',') === false || !array_filter($arr)) {
	                    continue 2;
	                }
	                //当出现一边为空时改变操作符
	                if ($arr[0] === '') {
	                    $sym = $sym == 'RANGE' ? '<=' : '>';
	                    $arr = $arr[1];
	                } elseif ($arr[1] === '') {
	                    $sym = $sym == 'RANGE' ? '>=' : '<';
	                    $arr = $arr[0];
	                }
	                $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
	                break;
	            case 'LIKE':
	            case 'LIKE %...%':
	                $where[] = [$k, 'LIKE', "%{$v}%"];
	                break;
	            case 'NULL':
	            case 'IS NULL':
	            case 'NOT NULL':
	            case 'IS NOT NULL':
	                $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
	                break;
	            default:
	                break;
	        }
	    }
	    $where = function ($query) use ($where) {
	        foreach ($where as $k => $v) {
	            if (is_array($v)) {
	                call_user_func_array([$query, 'where'], $v);
	            } else {
	                $query->where($v);
	            }
	        }
	    };
	    return [$where, $sort, $order];
	}
	
}
