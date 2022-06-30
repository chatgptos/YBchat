<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use fast\Random;
use think\Db;

/**
 * flbooth优惠券接口
 */
class Coupon extends Api
{
    protected $noNeedLogin = ['getList', 'query'];
	protected $noNeedRight = ['*'];
    
	/**
	 * 获取优惠券列表
	 *
	 * @ApiSummary  (flbooth 优惠券接口获取优惠券列表)
	 * @ApiMethod   (GET)
	 * 2020年9月15日17:44:57
	 *
	 * @param string $type 类型
	 */
	public function getList($type = null)
	{
		$list = model('app\api\model\flbooth\Coupon')
			->where([
				'type' => $type,
				// 'rangetype' => 'all', 1.0.5升级 
				'invalid' => 0
			])
			->order('created desc')
			->paginate()
			->each(function($order, $key){
				$order['shop'] = $order->shop?$order->shop->visible(['shopname']):[];
				return $order;
			});
		$list?($this->success('ok',$list)):($this->error(__('网络繁忙')));
	}
	
	/**
	 * 查询我的优惠券
	 *
	 * @ApiSummary  (flbooth 优惠券接口查询我的优惠券)
	 * @ApiMethod   (GET)
	 * 2020年9月16日03:32:43
	 *
	 * @param string $goods_id 商品ID
	 * @param string $shop_id 店铺ID
	 * @param string $shop_category_id 分类ID
	 * @param string $price 价格 
	 */
	public function query($goods_id = null, $shop_id = null, $shop_category_id = null, $price = null)
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
		// 开始查询 方案一
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
					// 高级查询，比较数组，返回交集(array_intersect)如果和原数据数目相同则加入
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
		// 开始查询 二
		// $list = [];
		// //要追加一个排序 选出一个性价比最高的
		// foreach (model('app\api\model\flbooth\Coupon')->where([
		// 	'shop_id' => $shop_id,
		// 	'limit' => ['<=', intval($price)]
		// ])->select() as $row) { 
		// 	// 如果优惠券状态有效  或  无效且我的列表中存在的
		// 	if($row['invalid'] == 0 || ($row['invalid'] == 1 && array_key_exists($row['id'], $user_coupon))){
		// 		// 筛选出过期的
		// 		if(!($row['pretype'] == 'fixed' && strtotime($row['startdate']) >= time())){
		// 			// 高级查询，比较数组，返回交集如果和原数据数目相同则加入
		// 			if($row['rangetype'] == 'goods'){
		// 				$goods_id = explode(",",$goods_id);
		// 				if(count($goods_id) == count(array_intersect($goods_id, explode(",",$row['range'])))){
		// 					// 检查指定的键名是否存在于数组中
		// 					if(array_key_exists($row['id'], $user_coupon)){
		// 						$row['id'] = $user_coupon[$row['id']]['id'];
		// 						$row['state'] = true;
		// 					}else{
		// 						$row['state'] = false;
		// 					}
		// 					$row['choice'] = false;
		// 					$list[] = $row;
		// 				}
		// 			}else if($row['rangetype'] == 'category'){
		// 				$shop_category_id = explode(",",$shop_category_id);
		// 				if(count($shop_category_id) == count(array_intersect($shop_category_id, explode(",",$row['range'])))){
		// 					if(array_key_exists($row['id'], $user_coupon)){
		// 						$row['id'] = $user_coupon[$row['id']]['id'];
		// 						$row['state'] = true;
		// 					}else{
		// 						$row['state'] = false;
		// 					}
		// 					$row['choice'] = false;
		// 					$list[] = $row;
		// 				}
		// 			}else{
		// 				if(array_key_exists($row['id'], $user_coupon)){
		// 					$row['id'] = $user_coupon[$row['id']]['id'];
		// 					$row['state'] = true;
		// 				}else{
		// 					$row['state'] = false;
		// 				}
		// 				$row['choice'] = false;
		// 				$list[] = $row;
		// 			}
		// 		}
		// 	}
		// }
		return $this->success('ok', $list);
	}
	
	/**
	 * 获取我的优惠券列表
	 *
	 * @ApiSummary  (flbooth 优惠券接口获取我的优惠券列表)
	 * @ApiMethod   (GET)
	 * 2020年9月16日08:09:17
	 *
	 * @param string $state 类型
	 */
	public function getMyList($state = null)
	{
		$list = model('app\api\model\flbooth\CouponReceive')
			->where([
				'state' => $state, 
				'user_id' => $this->auth->id
			])
			->order('created desc')
			->paginate()
			->each(function($order, $key){
				$order['shop'] = $order->shop?$order->shop->visible(['shopname']):[];
				return $order;
			});
		$list?($this->success('ok',$list)):($this->error(__('网络繁忙')));
	}
	
	/**
	 * 领取优惠券
	 *
	 * @ApiSummary  (flbooth 优惠券接口领取优惠券)
	 * @ApiMethod   (POST)
	 * 2020年9月16日03:32:43
	 *
	 * @param string $id 优惠券ID
	 */
	public function receive()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$id = $this->request->post('id');
			$user_id = $this->auth->id;
			$coupon = model('app\api\model\flbooth\Coupon')->get($id);
			if(!$coupon){
				$this->error(__('网络繁忙或优惠券不存在'));
			}
			// 查询此ID领取几张
			$myCouponCount = model('app\api\model\flbooth\CouponReceive')
				->where(['coupon_id' => $id, 'user_id' => $user_id])
				->count();
			// 判断是否发完
			if($coupon['drawlimit'] != 0){
				if($myCouponCount >= $coupon['drawlimit']){
					$this->error(__('亲，您已领取了'.$myCouponCount.'张，不能在领了！'));
				}
			}
			// 判断是否超出总数量
			if($coupon['grant'] != '-1'){
				if($coupon['alreadygrant'] >= intval($coupon['grant'])  || $coupon['surplus'] == 0){
					$this->error(__('亲，您来晚了，刚刚被抢完！'));
				}
			}
			// 判断优惠券是否过期
			if($coupon['pretype'] == 'fixed'){
				if(time() > strtotime($coupon['enddate'])){
					$this->error(__('此张优惠券已经过期了'));
				}
			}
			// 领取优惠券并保留备份
			$result = model('app\api\model\flbooth\CouponReceive');
				$result->state = 1;
				$result->coupon_id = $id;
				$result->coupon_no = Random::alnum(16);
				$result->user_id = $user_id;
				$result->shop_id = $coupon['shop_id'];
				$result->type = $coupon['type'];
				$result->name = $coupon['name'];
				$result->userlevel = $coupon['userlevel'];
				$result->usertype = $coupon['usertype'];
				$result->price = $coupon['price'];
				$result->discount = $coupon['discount'];
				$result->limit = $coupon['limit'];
				$result->rangetype = $coupon['rangetype'];
				$result->range = $coupon['range'];
				$result->pretype = $coupon['pretype'];
				$result->validity = $coupon['validity'];
				$result->startdate = $coupon['startdate'];
				$result->enddate = $coupon['enddate'];
				$result->save();
			if($result){
				if($coupon['grant'] != '-1'){
					// 剩余数量
					$data['surplus'] = $coupon['surplus'] - 1;
					// 即将过期，强制失效
					if($coupon['surplus'] == 1){
						$data['invalid'] = 1;
					}
				}
				$data['alreadygrant'] = $coupon['alreadygrant'] + 1;
				// 更新优惠券领取+1
				$coupon->allowField(true)->save($data);
				$this->success(__('ok'),['msg' => '恭喜此券，成功领取第 '.($myCouponCount+1).' 张','id' => $result['id']]);
			}else{
				$this->error(__('网络繁忙，领取失败'));
			}
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 立即使用优惠券
	 *
	 * @ApiSummary  (flbooth 优惠券接口立即使用优惠券)
	 * @ApiMethod   (GET)
	 * 2020年9月16日03:32:43
	 * 2020年11月6日10:03:08 1.0.2升级
	 *
	 * @param string $id 优惠券ID
	 */
	public function apply($id = null)
	{
		$coupon = model('app\api\model\flbooth\CouponReceive')->get($id);
		if($coupon){
			$where['shop_id'] = $coupon['shop_id'];
			$where['price'] = ['EGT', $coupon['limit']];
			// 指定商品
			if($coupon['rangetype'] == 'goods'){
				$where['id'] = ['IN', $coupon['range']];
			}
			// 指定分类
			if($coupon['rangetype'] == 'category'){
			    // 1.1.2升级
			    $where[] = ['EXP', Db::raw('FIND_IN_SET('.$coupon->range.', shop_category_id)')];
			}
			$list = model('app\api\model\flbooth\Goods')
				->where($where)
				->order('created desc')
				->paginate();
			foreach ($list as $row) {
				$row->shop->visible(['id','avatar','shopname']);
			}		
			$this->success('ok', ['coupon' => $coupon, 'goods' => $list]);
		}
		$this->error(__('非正常请求'));
	}
	
	
	
}