<?php
namespace app\api\controller\flbooth\groups;

use addons\flbooth\library\WanlSdk\Common;
use app\common\controller\Api;
use think\Cache;

use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * flbooth 营销活动
 */
class Order extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
    
	/**
     * 获取营销活动-拼团订单列表 ---
     *
     * @ApiSummary  (flbooth 营销活动-拼团订单接口获取营销活动-拼团订单列表)
     * @ApiMethod   (GET)
	 * 2021年6月28日12:50:01
	 *
	 * @param string $state 状态
	 */
    public function getOrderList()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
    	$state = $this->request->request('state');
        if ($state && $state != 0) {
        	$where['state'] = $state;
        }
        $where['status'] = 'normal';
        $where['user_id'] = $this->auth->id;
		// 列表	
		$list = model('app\api\model\flbooth\groups\Order')
			->where($where)
			->field('id,shop_id,state')
			->order('modified desc')
			->paginate()
			->each(function($order, $key){
				$order['goods'] = model('app\api\model\flbooth\groups\OrderGoods')
					->where(['order_id'=> $order->id])
					->field('id,title,image,difference,price,market_price,group_price,group_type,group_no,people_num,is_ladder,ladder_id,number,refund_status')
					->select();
				// 获取支付 1.1.2升级
				$order['pay'] = model('app\api\model\flbooth\Pay')
					->where(['order_id' => $order->id, 'type' => 'groups'])
					->field('number, price, order_price, freight_price, discount_price, actual_payment')
					->find();
				$order['shop'] = $order->shop ? $order->shop->visible(['shopname']):[];
				return $order;
			});
		$list?($this->success('ok',$list)):($this->error(__('网络繁忙')));
    }
	
	/**
	 * 查询用户店铺订单记录 
	 *
	 * @ApiSummary  (查询用户店铺订单记录 )
	 * @ApiMethod   (POST)
	 *
	 * @param string $shop_id 店铺ID
	 */
	public function getOrderListToShop()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$shop_id = $this->request->post('shop_id');
			$shop_id ? '':($this->error(__('Invalid parameters')));
			$list = model('app\api\model\flbooth\Order')
				->where(['shop_id' => $shop_id, 'user_id' => $this->auth->id, 'status' => 'normal'])
				->field('id,shop_id,order_no,state')
				->order('modified desc')
				->select();
			// 订单状态:1=待支付,2=待发货,3=待收货,4=待评论,5=售后订单(已弃用),6=已完成,7=已取消
			foreach ($list as $row) {
			    $row['goods'] = model('app\api\model\flbooth\OrderGoods')
			    	->where(['order_id'=> $row->id])
			    	->field('id,title,image,difference,price,market_price,number,refund_status')
			    	->select();
			}
			$this->success(__('发送成功'), $list);
		}
		$this->error(__('非法请求'));
	}
	
    /**
     * 获取订单详情 ----
     *
     * @ApiSummary  (flbooth 订单接口获取订单详情)
     * @ApiMethod   (GET)
	 * 
	 * @param string $id 订单ID
	 */
    public function getOrderInfo()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->request('id');
		$id ? $id : ($this->error(__('非法请求')));
		$order = model('app\api\model\flbooth\groups\Order')
			->where(['id' => $id, 'user_id' => $this->auth->id])
			->field('id,shop_id,order_no,isaddress,express_no,express_name,
			freight_type,state,created,paymenttime,delivertime,taketime,dealtime')
			->find();
		$order ? $order : ($this->error(__('网络繁忙')));
		// 输出配置
		$config = get_addon_config('flbooth');
		$order['config'] = $config['order'];
		switch ($order['state']) {
			case 1:
				$express = [
					'context' => '付款后，完成营销活动-拼团即可将宝贝发出',
					'status' => '尚未付款',
					'time' => date('Y-m-d H:i:s', $order['created'])
				];
				break;
			case 2:
				$express = [
					'context' => '正在分享营销活动-拼团中',
					'status' => '已付款',
					'time' => date('Y-m-d H:i:s', $order['created'])
				];
				break;
			case 3:
				$express = [
					'context' => '商家正在处理订单',
					'status' => '已付款',
					'time' => date('Y-m-d H:i:s', $order['paymenttime'])
				];
				break;
			default: // 获取物流
				$eData = model('app\api\model\flbooth\KuaidiSub')
					->where(['express_no' => $order['express_no']])
					->find();
				$ybData = json_decode($eData['data'], true);
				if($ybData){
					$express = $ybData[0];
				}else{
					$express = [
						'status' => '已发货',
						'context' => '包裹正在等待快递小哥揽收~',
						'time' => date('Y-m-d H:i:s', $order['delivertime'])
					];
				}
		}
		// 获取物流
		$order['logistics'] = $express;
		// 获取地址
		$order['address'] = model('app\api\model\flbooth\groups\OrderAddress')
			->where(['order_id' => $id, 'user_id' => $this->auth->id])
			->order('isaddress desc')
			->field('id,name,mobile,address,address_name')
			->find();
		// 获取店铺
		$order['shop'] = $order->shop?$order->shop->visible(['shopname']):[];
		// 获取订单商品
		$order['goods'] = model('app\api\model\flbooth\groups\OrderGoods')
			->where(['order_id'=> $id])
			->field('id,goods_id,title,image,difference,price,market_price,actual_payment,discount_price,freight_price,group_type,group_no,is_alone,is_ladder,ladder_id,people_num,group_hour,group_price,number,refund_id,refund_status')
			->select();
		// 获取支付 1.1.2升级
		$order['pay'] = model('app\api\model\flbooth\Pay')
			->where(['order_id' => $order->id, 'type' => 'groups'])
			->field('id, pay_no, number, price, order_price, freight_price, discount_price, actual_payment')
			->find();
		$this->success('ok',$order);
    }
	
	/**
	 * 取消订单 ----
	 *
	 * @ApiSummary  (flbooth 订单接口取消订单)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	public function cancelOrder()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			$id ? $id : ($this->error(__('非法请求')));
			// 判断权限
			$this->getOrderState($id) != 1 ? ($this->error(__('订单异常'))):'';
			$row = model('app\api\model\flbooth\groups\Order')->get(['id' => $id, 'user_id' => $this->auth->id]);
			$result = $row->allowField(true)->save(['state' => 7]);
			
			// 还原优惠券
			if($row['coupon_id'] != 0){
				model('app\api\model\flbooth\CouponReceive')->where(['id' => $row['coupon_id'], 'user_id' => $this->auth->id])->update(['state' => 1]);
			}
			// 释放库存
			foreach(model('app\api\model\flbooth\groups\OrderGoods')->all(['order_id' => $row['id']]) as $vo){
				// 查询商品是否需要释放库存
				if(model('app\api\model\flbooth\groups\Goods')->get($vo['goods_id'])['stock'] == 'porder'){
					model('app\api\model\flbooth\groups\GoodsSku')->where('id', $vo['goods_sku_id'])->setInc('stock', $vo['number']);
				}
				// 删除营销活动-拼团-1.0.8临时方案
				model('app\api\model\flbooth\groups\Groups')
					->where([
						'group_no' => $vo['group_no'], 
						'user_id' => $this->auth->id,
						'state' => 'ready'
					])
					->setField('deleted', time());
			}
		    $this->success('ok', $result ? true : false);
		}
		$this->error(__('非法请求'));
	}
	
    /**
     * 删除订单 -----
     *
     * @ApiSummary  (flbooth 订单接口删除订单)
     * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
    public function delOrder()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
        if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			$id ? $id : ($this->error(__('非法请求')));
			// 判断权限
			$state = $this->getOrderState($id);
			$state == 6 || $state == 7 ? '' :($this->error(__('非法请求')));
			$order = model('app\api\model\flbooth\groups\Order')
				->save(['status' => 'hidden'],['id' => $id]);
			$this->success('ok', $order ? true : false);
		}
		$this->error(__('非法请求'));
    }
	
    
	/**
	 * 修改地址 ----
	 *
	 * @ApiSummary  (flbooth 订单接口修改地址)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	public function editOrderAddress()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $order_id = $this->request->post('id');
			$address_id = $this->request->post('address_id');
			$order_id || $address_id ? $order_id : ($this->error(__('非法请求')));
			// 判断权限
			$this->getOrderState($order_id) > 3 ? ($this->error(__('订单异常'))):'';
			// 订单
			$order = model('app\api\model\flbooth\groups\Order')
				->where(['id' => $order_id, 'user_id' => $this->auth->id])
				->find();
			
			//判断是否修改过
			if($order['isaddress'] == 1){
				$this->error(__('已经修改过一次了'));
			}else{
				// 获取地址
				$address = model('app\api\model\flbooth\Address')
					->where(['id' => $address_id, 'user_id' => $this->auth->id])
					->find();
				// 修改地址
				$data = model('app\api\model\flbooth\groups\OrderAddress')->save([
						'user_id' => $this->auth->id,
						'shop_id' => $order->shop_id,
						'order_id'  => $order_id,
						'isaddress' => 1,
						'name' => $address['name'],
						'mobile' => $address['mobile'],
						'address' => $address['province'].'/'.$address['city'].'/'.$address['district'].'/'.$address['address'],
						'address_name' => $address['address_name'],
						'location' => $address['location']
					]);
				// 修改状态
				model('app\api\model\flbooth\groups\Order')->where(['id' => $order_id, 'user_id' => $this->auth->id])->update(['isaddress' => 1]);
				$this->success('ok',$data);
			}
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 确认收货 ----
	 *
	 * @ApiSummary  (flbooth 订单接口确认收货)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID                       ------ 后续版本优化 Db::startTrans();
	 */
	public function confirmOrder()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			$id ? $id : ($this->error(__('非法请求')));
			// 判断权限
			$order = model('app\api\model\flbooth\groups\Order')
				->where(['id' => $id, 'state'=> 4, 'user_id' => $this->auth->id])
				->find();
			if(!$order){
				$this->error(__('订单异常'));
			}
			// 获取支付 1.1.2升级
			$pay = model('app\api\model\flbooth\Pay')->get(['order_id' => $id, 'type' => 'groups']);
			// 平台转款给商家
			controller('addons\flbooth\library\WanlPay\WanlPay')->money(+$pay['price'], $order['shop']['user_id'], '买家确认收货', 'pay', $order['order_no']);
			// 查询是否有退款 1.1.2升级
			$refund = model('app\api\model\flbooth\Refund')
				->where(['order_id' => $id, 'state' => 4, 'order_type' => 'groups'])
				->select();
			// 退款存在
			if($refund){
				foreach($refund as $value){
					controller('addons\flbooth\library\WanlPay\WanlPay')->money(-$value['price'], $order['shop']['user_id'], '该订单存在的退款', 'pay', $order['order_no']);
				}
			}
			// 更新退款
			$order->save(['state' => 5,'taketime' => time()],['id' => $id]);
		    $this->success('ok', $order ? true : false);
		}
		$this->error(__('非法请求'));
	}
	
	
	
	/**
	 * 评论订单
	 *
	 * @ApiSummary  (flbooth 订单接口评论订单)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	public function commentOrder()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $post = $this->request->post();
			$post ? $post : ($this->error(__('数据异常')));
			$user_id = $this->auth->id;
			// 判断权限
			$this->getOrderState($post['order_id']) != 5 ? ($this->error(__('已经评论过或订单异常'))):'';
			// 生成列表
			$commentData = [];
			foreach ($post['goodsList'] as $value) {
				$commentData[] = [
					'user_id' => $user_id,
					'shop_id' => $post['shop']['id'],
					'order_id' => $post['order_id'],
					'goods_id' => $value['goods_id'],
					'order_type' => 'groups',
					'order_goods_id' => $value['id'],
					'state' => $value['state'],
					'content' => $value['comment'],
					'suk' => $value['difference'],
					'images' => $value['imgList'],
					'score' => round((($post['shop']['describe'] + $post['shop']['service'] + $post['shop']['deliver'] + $post['shop']['logistics']) / 4) ,1),
					'score_describe' => $post['shop']['describe'],
					'score_service' => $post['shop']['service'],
					'score_deliver' => $post['shop']['deliver'],
					'score_logistics' => $post['shop']['logistics'],
					'switch' => 0
				];
				//评论暂不考虑并发，为列表提供好评付款率，减少并发只能写进商品中
				model('app\api\model\flbooth\groups\Goods')->where(['id' => $value['goods_id']])->setInc('comment');
				if($value['state'] == 0){
					model('app\api\model\flbooth\groups\Goods')->where(['id' => $value['goods_id']])->setInc('praise');
				}else if($value['state'] == 1){
					model('app\api\model\flbooth\groups\Goods')->where(['id' => $value['goods_id']])->setInc('moderate');
				}else if($value['state'] == 2){
					model('app\api\model\flbooth\groups\Goods')->where(['id' => $value['goods_id']])->setInc('negative');
				}
			}
			if(model('app\api\model\flbooth\GoodsComment')->saveAll($commentData)){
				$order = model('app\api\model\flbooth\groups\Order')
					->where(['id' => $post['order_id'], 'user_id' => $user_id])
					->update(['state' => 6]);
			}
			//更新店铺评分
			$score = model('app\api\model\flbooth\GoodsComment')
				->where(['user_id' => $user_id])
				->select();
			// 从数据集中取出
			$describe = array_column($score,'score_describe');
			$service = array_column($score,'score_service');
			$deliver = array_column($score,'score_deliver');
			$logistics = array_column($score,'score_logistics');
			// 更新店铺评分
			model('app\api\model\flbooth\Shop')
				->where(['id' => $post['shop']['id']])
				->update([
					'score_describe' => bcdiv(array_sum($describe), count($describe), 1),
					'score_service' => bcdiv(array_sum($service), count($service), 1),
					'score_deliver' => bcdiv(array_sum($deliver), count($deliver), 1),
					'score_logistics' => bcdiv(array_sum($logistics), count($logistics), 1)
				]);
		    $this->success('ok',[]);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 获取订单物流状态 ----
	 *
	 * @ApiSummary  (flbooth 订单接口获取订单物流状态)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	public function getLogistics()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			$id ? $id : ($this->error(__('非法请求')));
			//获取订单
			$order = model('app\api\model\flbooth\groups\Order')
				->where(['id' => $id, 'user_id' => $this->auth->id])
				->field('id,shop_id,express_name,express_no,order_no,created,paymenttime,delivertime')
				->find();
			// 获取快递
			switch ($order['state']) {
				case 1:
					$express[] = [
						'context' => '付款后，即可将宝贝发出',
						'status' => '尚未付款',
						'time' => date('Y-m-d H:i:s', $order['created'])
					];
					break;
				case 2:
					$express[] = [
						'context' => '商家接受到您的订单，准备出库',
						'status' => '已下单',
						'time' => date('Y-m-d H:i:s', $order['paymenttime'])
					];
					break;
				default: // 获取物流
					$express = model('app\api\model\flbooth\KuaidiSub')
						->where(['express_no' => $order['express_no']])
						->find();
					if($express){
						$express = json_decode($express['data'], true);
					}else{
						$express[] = [
							'context' => '打包完成，正在等待快递小哥揽收~',
							'status' => '仓库处理中',
							'time' => date('Y-m-d H:i:s', $order['delivertime'])
						];
					}
			}
			$order['kuaidi'] = $order->kuaidi ? $order->kuaidi->visible(['name','logo','tel']) : [];
			$order['express'] = $express;
		    $this->success('ok',$order);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 查询购买次数限制
	 *
	 * @ApiSummary  (flbooth 营销活动-拼团接口查询购买次数限制)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $data 商品数据
	 */
	public function getOrderPurchaseLimit($id = 0)
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$goods = model('app\api\model\flbooth\groups\Goods')
			->where('id', $id)
			->find();
		if($goods){
			if($goods['purchase_limit'] != 0){
				$orderGoodsConunt = model('app\api\model\flbooth\groups\OrderGoods')
					->where(['goods_id' => $id, 'user_id' => $this->auth->id])
					->count();
				if($orderGoodsConunt >= $goods['purchase_limit']){
					$this->error(__('此商品每个ID仅可购买'.$goods['purchase_limit'].'件，你已购买过'. $orderGoodsConunt .'件'));
				}
			}
			$this->success('不满足限制条件');
		}else{
			$this->error(__('非法请求'));
		}
	}
	
    /**
     * 确认营销活动-拼团订单
     *
     * @ApiSummary  (flbooth 营销活动-拼团接口确认订单)
     * @ApiMethod   (POST)
	 * 
	 * @param string $data 商品数据
	 */
    public function getOrderGoodsList()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
        if ($this->request->isPost()) {
		    $request = $this->request->post();
		    // 订单数据
		    $order = array();
		    $map = array();
			// 客户端地址更新
			$where = !empty($request['address_id']) ? ['id' => $request['address_id'], 'user_id' => $this->auth->id] : ['user_id' => $this->auth->id, 'default' => 1];
			// 地址
			$address = model('app\api\model\flbooth\Address')
				->where($where)
			    ->field('id,name,mobile,province,city,district,address')
				->find();
		    // 合计
		    $statis = array(
				"allnum" => 0,
				"allsub" => 0
			);
		    foreach ($request['data'] as $post) {
				$redis = Common::redis();
		    	// 商品详情
		    	$goods = model('app\api\model\flbooth\groups\Goods')
		    		->where('id', $post['goods_id'])
		    	    ->field('id,shop_id,shop_category_id,title,image,
						is_ladder,is_alone,is_discount,discount_type,discount,
						stock,freight_id,sales')
		    	    ->find();
		    	// 获取SKU
				$sku = model('app\api\model\flbooth\groups\GoodsSku')
		    		->where('id', $post['sku_id'])
		    	    ->field('id,goods_id,difference,price,market_price,stock,weigh')
		    	    ->find();
				// 1.1.2升级 判断是否超出库存
				$sku_key = 'groups_'.$sku['goods_id'].'_'.$sku['id'];
				if($post['number'] > $redis->llen("{$sku_key}")){
					$this->error("系统繁忙，请稍后抢购！");
				}
				// 判断团购
				if($post['type'] === 'group'){
					// 判断是否阶梯团
					if($goods['is_ladder'] === 1){
						$ladder = model('app\api\model\flbooth\groups\Ladder')
							->where(['id' => $post['ladder_id']])
							->field('id,people_num,discount')
							->find();
						if($ladder){
							$price = bcdiv(bcmul($sku['price'], $ladder['discount'], 2), 100, 2);
							// 团购阶梯
							$goods['ladder'] = $ladder;
							$goods['groupType'] = 'ladder';
						}else{
							$this->error(__('查询团购阶梯失败'));
						}
					}else{
						$price = $sku['price'];
						$goods['groupType'] = 'group';
					}
				}else{
					if($goods['is_alone'] === 0){
						$this->error(__('订单暂时不支持单独购买'));
					}
					$price = $sku['market_price'];
					$goods['groupType'] = 'alone';
				}
				$goods['groups_id'] = $post['groups_id'];
				// 获取快递及价格
				$goods['freight'] = $this->freight($goods['freight_id'], $sku['weigh'], $post['number'], $address['city']);
				// 获取SKU
		    	$goods['sku'] = $sku;
		    	// 数量
		    	$goods['number'] = $post['number'];
			    // 格式化
		        if (empty($map[$goods['shop_id']])) {
		            $order[] = array(
					    "shop_id"   => $goods['shop_id'],
					    "shop_name" => $goods->shop ? $goods->shop->visible(['shopname'])['shopname']:[],
					    "products"  => [$goods],
						"coupon" => [],
						"freight"  => [$goods['freight']],
					    "number"    => $goods['number'],
						"sub_price" => bcmul($price, $goods['number'], 2)
					);
		            $map[$goods['shop_id']] = $goods;
		        } else {
					// 追加1-*
		            foreach ($order as $key => $value) {
		                if ($value['shop_id'] == $goods['shop_id']) {
		            		array_push($order[$key]['products'], $goods);
							array_push($order[$key]['freight'], $goods['freight']);
		            		$order[$key]['number'] += $post['number'];
							$order[$key]['sub_price'] = bcadd($order[$key]['sub_price'], bcmul($price, $post['number'], 2), 2);
		                    break;
		                }
		            }
		        }
				// 所有店铺统计
				$statis['allnum'] += $goods['number'];
		    }
			// 获取运费策略-店铺循环
			foreach ($order as $key => $value) {
				$config = model('app\api\model\flbooth\ShopConfig')
					->where('shop_id',$value['shop_id'])
					->find();
				if($config['freight'] == 0){
					// 运费叠加
					$order[$key]['freight'] = [
						'id' => $value['freight'][0]['id'],
						'name' => '运费叠加',
						'price' => number_format(array_sum(array_column($value['freight'],'price')), 2)
					];
				}else if($config['freight'] == 1){
					// 以最低结算
					array_multisort(array_column($value['freight'],'price'),SORT_ASC,$value['freight']);
					$order[$key]['freight'] = [
						'id' => $value['freight'][0]['id'],
						'name' => $value['freight'][0]['name'],
						'price' => number_format($value['freight'][0]['price'], 2)
					];
				}else if($config['freight'] == 2){
					// 以最高结算
					array_multisort(array_column($value['freight'],'price'),SORT_DESC,$value['freight']);
					$order[$key]['freight'] = [
						'id' => $value['freight'][0]['id'],
						'name' => $value['freight'][0]['name'],
						'price' => number_format($value['freight'][0]['price'], 2)
					];
				}
				$order[$key]['order_price'] = $order[$key]['sub_price'];
				// 2020年9月19日12:10:59 添加快递价格备份,用于还原运费
				$order[$key]['freight_price_bak'] = number_format($order[$key]['freight']['price'], 2);
				// 1.0.8升级
				$order[$key]['sub_price'] = bcadd($order[$key]['sub_price'], $order[$key]['freight']['price'], 2);
				$statis['allsub'] = bcadd($statis['allsub'], $order[$key]['sub_price'], 2);
			}
			// 传递Token
			$datalist['token'] = self::creatToken();
		    // 地址
		    $datalist['addressData'] = $address;
			// 订单
		    $datalist['orderData']['lists'] = $order;
		    $datalist['orderData']['statis'] = $statis;
		    $this->success('ok', $datalist);
		} else {
		    $this->error(__('非法请求'));
		}
    }
    
    /**
     * 提交订单
     *
     * @ApiSummary  (flbooth 订单接口提交订单)
     * @ApiMethod   (POST)
     * 
     * @param string $data 数组
     */
    public function addOrder()
    {
    	//设置过滤方法
    	$this->request->filter(['strip_tags']);
        if ($this->request->isPost()) {
			$result = false;
    		$params = $this->request->post();
			// 验证Token
			if(array_key_exists('token', $params)){
				if(!self::checkToken($params['token'])){
					$this->error(__('页面安全令牌已过期！请重返此页'));
				}
			}else{
				$this->error(__('非法提交，未传入Token'));
			}
    		$user_id = $this->auth->id;
			$addressList = [];
			$goodsList = [];
			$groupsList = [];
			$payList = [];
    		if(array_key_exists('address_id',$params['order'])){
    			$address_id = $params['order']['address_id']; // 地址ID
    		}else{
    			$this->error(__('请点击上方添加收货地址'));
    		}
    		// 判断订单是否合法
    		if(array_key_exists('lists',$params['order'])){
    			$lists = $params['order']['lists'];
    			if(!isset($lists) && count($lists) == 0){
    				$this->error(__('订单繁忙ERR001：请返回商品详情重新提交订单'));
    			}
    		}else{
    			$this->error(__('订单繁忙ERR002：请返回商品详情重新提交订单'));
    		}
    		// 查询地址
    		$address = model('app\api\model\flbooth\Address')
    			->where(['id' => $address_id,'user_id' =>$user_id])
    			->find();
    		if(!isset($address)){
    			$this->error(__('地址异常，没有找到该地址'));
    		}
			
			// 数据库事务操作
    		Db::startTrans();
    		try {
				// 遍历已店铺分类列表
				foreach ($lists as $item) {
					// 1.0.6升级 修复跨店累加
					$priceAll = 0; // 总价格
					$numberAll = 0; // 总数量
					$freightALL = [];
					$coupon_price = 0; //优惠券金额
					$discount_price = 0; // 优惠金额，因为后续版本涉及到活动减免，所以优惠金额要单独拎出来
					// 获取店铺ID
					$shop_id = $item['shop_id'];
					// 查询店铺配置
					$config = model('app\api\model\flbooth\ShopConfig')
						->where('shop_id', $shop_id)
						->find();
					// 如果不存在，按照累计运费
					if(!$config){
						$config['freight'] = 0;
					}
					// 生成订单
					$order = new \app\api\model\flbooth\groups\Order;
					$order->freight_type = $config['freight'];
					$order->user_id = $user_id;
					$order->shop_id = $shop_id;
					$order->order_no = $shop_id.$user_id;
					if(isset($item['remarks'])){
					    $order->remarks = $item['remarks'];
					}
					// 2020年9月19日 05:30:10 新增优惠券功能 
					// 2021年3月04日 06:54:11 修改优惠券逻辑
					$coupon = model('app\api\model\flbooth\CouponReceive')
						->where(['id' => $item['coupon_id'], 'user_id' => $user_id, 'shop_id' => $shop_id])
						->find();
					$order->coupon_id = $coupon ? $coupon['id'] : 0;
					// 要补充活动ID
					if($order->save()){
						// 计算订单价格
						foreach ($item['products'] as $data){
							$redis = Common::redis();
							// 查询商品
							$goods = model('app\api\model\flbooth\groups\Goods')->get($data['goods_id']);
							// 判断是否为阶梯营销活动-拼团
							$isLadder = false;
							if($goods['is_ladder'] === 1 && $data['groupType'] === 'ladder'){
								$ladder = model('app\api\model\flbooth\groups\Ladder')->get($data['ladder_id']);
								if($ladder){
									$isLadder = true;
								}else{
									throw new Exception('查询团购阶梯失败');
								}
							}
							// 获取sku
							$sku = model('app\api\model\flbooth\groups\GoodsSku')->get($data['sku_id']);
							// 1.1.2升级
							$sku_key = 'groups_'.$sku['goods_id'].'_'.$sku['id'];
							// 1.1.0升级
							if(!$goods) throw new Exception('对不起当前商品不存在或已下架！');
							// 效验shop_id是否正确 1.1.2升级
							if($goods['shop_id'] != $shop_id) throw new Exception('网络异常SHOPID错误！');
							// 1.1.2升级 提交订单判断库存
							if($sku['stock'] <= 0){
								throw new Exception('商品被抢光了');
							}else if($sku['stock'] < $data['number']){
								throw new Exception('库存不足');
							}
							// 库存计算方式:porder=下单减库存,payment=付款减库存
							if($goods['stock'] == 'porder'){
								// 1.1.2升级
								if($data['number'] > $redis->llen("{$sku_key}")){
									throw new Exception('系统繁忙，请稍后抢购！');
								}else{
									for ($i = 0; $i < $data['number']; $i ++) {
										$redis->rpop("{$sku_key}");
									}
									$sku->setDec('stock', $data['number']); // 1.0.3升级
								}
							}
							$group_no = '';
							// 判断是否支持直购
							if($data['groupType'] === 'alone'){
								if($goods['is_alone'] === 0){
									throw new Exception('订单暂时不支持单独购买');
								}
								$goodsPrice = $sku['market_price'];
							}else{
								// 查询限制 1.1.0升级
								if($goods['purchase_limit'] != 0){
									$orderGoodsConunt = model('app\api\model\flbooth\groups\OrderGoods')
										->where(['goods_id' => $data['goods_id'], 'user_id' => $this->auth->id])
										->count();
									if($orderGoodsConunt >= $goods['purchase_limit']){
										throw new Exception('此商品每个ID仅可购买'.$goods['purchase_limit'].'件，你已购买过'. $orderGoodsConunt .'件');
									}
								}
								// 判断参团权限 如果参团存
								if((int)$data['groups_id'] !== 0){
									// 判断权限
									$joinGroups = model('app\api\model\flbooth\groups\Groups')->get($data['groups_id']);
									// 获取其他团的营销活动-拼团人数
									if($joinGroups['group_type'] === 'ladder'){
										$ladder = model('app\api\model\flbooth\groups\Ladder')->get($joinGroups['ladder_id']);
										$data['groupType'] = 'ladder';
										if(!$ladder){
											throw new Exception('查询团购阶梯失败');
										}
									}else{
										$isLadder = false;
									}
									// 判断状态
									if($joinGroups['state'] !== 'start'){
										$joinGroupsText = ['ready' => '准备中','success' => '已成团','fail' => '营销活动-拼团关闭','auto' => '已成团'][$joinGroups['state']];
										throw new Exception('参与拼单失败，因选择的团'.$joinGroupsText);
									}
									// 判断拼自己的团
									if($joinGroups['user_id'] === $this->auth->id){
										throw new Exception("参与拼单失败，不可拼自己的团");
									}
									// 判断是否超团
									if(model('app\api\model\flbooth\groups\Team')->where(['group_no' => $joinGroups['group_no']])->count() >= $joinGroups['people_num']){
										throw new Exception("参与拼单失败，因营销活动-拼团已完成");
									}
									$group_no = $joinGroups['group_no'];
								}else{
									$group_no = date('YmdHis') . rand(10000000,99999999);
									// 新建团
									$groupsList[] = [
										'group_no' => $group_no,
										'user_id' => $this->auth->id,
										'shop_id' => $shop_id,
										'goods_id' => $goods['id'],
										// 'goods_sku_id' => $sku['id'],
										'group_type' => $data['groupType'], // 营销活动-拼团类型
										'is_ladder' => $goods['is_ladder'], // 是否阶梯
										'ladder_id' =>  $isLadder ? $ladder['id'] : 0, // 阶梯ID
										'people_num' => $isLadder ? $ladder['people_num'] : $goods['people_num'], //成团人数
										'state' => 'ready', // 未开始
										'validitytime' => time() + $goods['group_hour'] * 60 * 60  // 营销活动-拼团有效期
									];
								}
								// 查询团购阶梯
								if($data['groupType'] === 'ladder'){
									$goodsPrice = bcdiv(bcmul($sku['price'], $ladder['discount'], 2), 100, 2);
								}else if($data['groupType'] === 'group' ){
									$goodsPrice = $sku['price'];
								}else{
									throw new Exception("非法访问");
								}
							}
							// 生成运费
							$freight = $this->freight($goods['freight_id'], $sku['weigh'], $data['number'], $address['city']);
							// 商品列表 actual_payment
							$goodsList[] = [
								'order_id' => $order->id, // 获取自增ID
								'goods_id' => $goods['id'],
								'user_id' => $this->auth->id,
								'shop_id' => $shop_id,
								'title' => $goods['title'],
								'image' => $goods['image'],
								// 营销活动-拼团类型
								'group_type' => $data['groupType'], 
								'group_no' => $group_no, 
								'is_alone' => $goods['is_alone'], // 是否单购是
								'is_ladder' => $goods['is_ladder'], // 开启阶梯团
								'ladder_id' => $isLadder ? $ladder['id'] : 0,
								'people_num' => $isLadder ? $ladder['people_num'] : $goods['people_num'], // 组团人数
								'purchase_limit' => $goods['purchase_limit'], // 购买次数限制
								'group_hour' => $goods['group_hour'], // 组团限时(整数小时)
								// 商品规格
								'goods_sku_sn' => $sku['sn'],
								'goods_sku_id' => $sku['id'],
								'difference' => join(',', $sku['difference']),
								'group_price' => $goodsPrice, // 团购价格
								'market_price' => $sku['market_price'], // 市场价
								'price' => $sku['price'], // 原价
								'freight_price' => $freight['price'], //快递价格
								'discount_price' => 0, // 优惠金额
								'actual_payment' => bcmul($goodsPrice, $data['number'], 2), // 1.0.6修复 实际支付，因为要和总价进行计算
								'number' => $data['number']
							];
							$freightALL[] = $freight;
							$priceAll = bcadd($priceAll, bcmul($goodsPrice, $data['number'], 2), 2); // 计算价格
							$numberAll += $data['number']; // 计算数量
						}
						// 计算运费叠加方案
						if($config['freight'] == 0){
							// 运费叠加
							$freight = [
								'id' => $freightALL[0]['id'],
								'name' => '合并运费',
								'price' => array_sum(array_column($freightALL,'price'))
							];
						}else if($config['freight'] == 1){ // 以最低结算
							array_multisort(array_column($freightALL,'price'),SORT_ASC,$freightALL);
							$freight = [
								'id' => $freightALL[0]['id'],
								'name' => $freightALL[0]['name'],
								'price' => $freightALL[0]['price']
							];
						}else if($config['freight'] == 2){ // 以最高结算
							array_multisort(array_column($freightALL,'price'),SORT_DESC,$freightALL);
							$freight = [
								'id' => $freightALL[0]['id'],
								'name' => $freightALL[0]['name'],
								'price' => $freightALL[0]['price']
							];
						}
						$freight_price = $freight['price'];  //快递金额
						$price = bcadd($priceAll, $freight_price, 2); // 总价格
						
						// 如果优惠券存在
						if($coupon) 
							list($price, $coupon_price, $freight_price, $discount_price, $goodsList) 
								= $this->coupon($coupon, $goodsList, $priceAll, $freight_price, $order->id);
						// 生成支付
						$payList[] = [
							'user_id' => $user_id,
							'shop_id' => $shop_id,
							'order_id'  => $order->id,
							'order_no'  => $order->order_no,
							'pay_no' => $order->order_no,
							'type' => 'groups', // 1.0.8升级
							'price'  => $price <= 0 ? 0.01 : $price,  // 支付价格，系统要求至少支付一分钱
							'order_price' => $priceAll, // 订单总金额
							'coupon_price' => $coupon_price,  // 优惠券金额
							'freight_price' => $freight_price, // 快递费
							'discount_price' => $discount_price, // 优惠金额
							'number'  => $numberAll
						];
						// 生成地址
						$addressList[] = [
							'user_id' => $user_id,
							'shop_id' => $shop_id,
							'order_id'  => $order->id,
							'name' => $address['name'],
							'mobile' => $address['mobile'],
							'address' => $address['province'].'/'.$address['city'].'/'.$address['district'].'/'.$address['address'],
							'address_name' => $address['address_name'],
							'location' => $address['location']
						];
					}else{
						throw new Exception("网络繁忙，创建订单失败！");
					}
				}
    		    model('app\api\model\flbooth\groups\OrderAddress')->saveAll($addressList);
    		    model('app\api\model\flbooth\groups\OrderGoods')->saveAll($goodsList);
				model('app\api\model\flbooth\groups\Groups')->saveAll($groupsList);
    		    $result = model('app\api\model\flbooth\Pay')->saveAll($payList);
    		    Db::commit();
    		} catch (PDOException $e) {
    		    Db::rollback();
    		    $this->error($e->getMessage());
    		} catch (Exception $e) {
    		    Db::rollback();
    		    $this->error($e->getMessage());
    		}
    		if ($result !== false) {
    		    $this->success('返回成功', [
					'list' => $result,
					'token' => self::creatToken()
				]);
    		} else {
    		    $this->error(__('订单异常，请返回重新下单'));
    		}
    	} else {
    	    $this->error(__('非法请求'));
    	}
    }
    
	/**
	 * 订单状态码（方法内使用）
	 *
	 * @ApiSummary  (flbooth 返回订单状态码)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	private function getOrderState($id = 0)
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    $order = model('app\api\model\flbooth\groups\Order')
	    	->where(['id' => $id, 'user_id' => $this->auth->id])
	    	->find();
		return $order['state'];
	}
	
	/**
	 * 营销活动-拼团 获取优惠券后金额 内部方法 
	 * @param string $coupon  优惠券数据
	 * @param string $goodsList  商品列表
	 * @param string $priceAll  订单总价格
	 * @param string $freight_price  运费价格
	 * @param string $order_id  订单ID
	 */
	private function coupon($coupon = [], $goodsList = [], $priceAll = 0, $freight_price = 0, $order_id = 0)
	{
		// 判断是否可用
		if($priceAll >= $coupon['limit']){
			if($coupon['type'] == 'reduction' || ($coupon['type'] == 'vip' && $coupon['usertype'] == 'reduction')){
				$coupon_price = $coupon['price']; 
			}
			if($coupon['type'] == 'discount' || ($coupon['type'] == 'vip' && $coupon['usertype'] == 'discount')){
				// 排除异常折扣，还原百分之
				$discount = $coupon['discount'] > 10 ? bcdiv($coupon['discount'], 100, 2) : bcdiv($coupon['discount'], 10, 2);
				// 优惠金额 = 订单金额 - 订单金额 * 折扣
				$coupon_price = bcsub($priceAll, bcmul($priceAll, $discount, 2), 2);
			}
			if($coupon['type'] == 'shipping'){
				$coupon_price = $freight_price;
				$freight_price = 0;
			}
			// 总优惠金额
			$paycoupon = 0;
			// 总实际支付金额
			$payment = 0;   
			// 更新商品列表
			$newGoodsList = [];
			foreach ($goodsList as $row) {
				$price = bcmul($row['price'], $row['number'], 2);
				$discount_price = round($coupon_price * ( $price / $priceAll ), 2); // 优惠金额
				// 1.0.8升级,修复包邮
				$actual_payment = $coupon['type'] === 'shipping' ? $price : bcsub($price, $discount_price, 2);
				//优惠价格
				$row['discount_price'] = $discount_price; 
				// 实际支付 1.0.9升级
				$row['actual_payment'] = $actual_payment <= 0 ? '0.01' : $actual_payment; 
				// 1.0.8升级
				$paycoupon = bcadd($paycoupon, $discount_price, 2); 
				$payment = bcadd($payment, $actual_payment, 2);
				$row['freight_price'] = $freight_price;
				$newGoodsList[] = $row;
			}
			// 更新已使用数量
			model('app\api\model\flbooth\Coupon')
				->where(['id' => $coupon['coupon_id']])
				->setInc('usenum');
			// 修改该优惠券状态 已用
			$coupon->allowField(true)->save(['state' => 2]);
			//总金额 = 总优惠后金额 + 运费
			return [bcadd($payment, $freight_price, 2), $paycoupon, $freight_price, $paycoupon, $newGoodsList];
		}else{
			model('app\api\model\flbooth\groups\Order')->destroy($order_id);
			$this->error('订单金额'.$priceAll.'元，不满'.$coupon['limit'].'元');
		}
	}
	
	
	/**
	 * 获取运费模板和子类 内部方法
	 * @param string $id  运费ID
	 * @param string $weigh  商品重量
	 * @param string $number  商品数量
	 * @param string $city  邮递城市
	 */
	private function freight($id = null, $weigh = null, $number = 0, $city = null)
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
			
			// 计价方式:0=按件数,1=按重量,2=按体积  1.0.2升级 
			if($data['valuation'] == 0){
				if($number <= $list['first']){
					$price = $list['first_fee'];
				}else{
					$additional = $list['additional'] > 0 ? $list['additional'] : 1; //因为要更换vue后台，临时方案，为防止客户填写0
					$price = bcadd(bcmul(ceil(($number - $list['first']) / $additional), $list['additional_fee'], 2), $list['first_fee'], 2);
				}
			}else{
				$weigh = $weigh * $number; // 订单总重量
				if($weigh <= $list['first']){ // 如果重量小于等首重，则首重价格
					$price = $list['first_fee'];
				}else{
					$additional = $list['additional'] > 0 ? $list['additional'] : 1;
					$price = bcadd(bcmul(ceil(($weigh - $list['first']) / $additional), $list['additional_fee'], 2), $list['first_fee'], 2);
				}
			}
			$data['price'] = number_format($price, 2);
		}
		return $data;
	}
	
	
	/**
	 * Redis连接
	 */
	private function wanlRedis() 
	{
		if (!extension_loaded('redis')) {
		    $this->error('服务器不支持Redis，请安装Redis和php redis拓展');
		}
		$config = get_addon_config('flbooth');
		$redis = new \Redis;
		if ($config['redis']['persistent'] == 'Y') {
		    $redis->pconnect($config['redis']['host'], $config['redis']['port'], $config['redis']['timeout'], 'persistent_id_' . $config['redis']['select']);
		} else {
		    $redis->connect($config['redis']['host'], $config['redis']['port'], $config['redis']['timeout']);
		}
		if ('' != $config['redis']['password']) {
		    $redis->auth($config['redis']['password']);
		}
		if (0 != $config['redis']['select']) {
		    $redis->select($config['redis']['select']);
		}
		if('+PONG' != $redis->ping()){
			$this->error($e->getMessage().'或未在后台配置Redis');
		}
		return $redis;
	}
	
	
	/**
	 * 创建Token
	 */
	private function creatToken() {
		$code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
		$key = "flbooth.COM";
		$code = md5($key . substr(md5($code), 8, 10));
		Cache::set('orderToken', $code);
		return $code;
	}
	
	/**
	 * 验证Token
	 * @param {Object} $token
	 */
	private function checkToken($token) 
	{
		if ($token == Cache::get('orderToken')) {
			Cache::set('orderToken', NULL);
			return TRUE;
		} else {
			return FALSE;
		}
	}
}