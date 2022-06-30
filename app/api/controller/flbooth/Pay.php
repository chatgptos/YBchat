<?php
namespace app\api\controller\flbooth;

use addons\flbooth\library\WanlPay\WanlPay;
use addons\flbooth\library\WanlSdk\Common;
use app\common\controller\Api;
use think\Cache;
use think\Db;
use think\Exception;


/**
 * flbooth支付接口
 */
class Pay extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
    
	/**
	 * 获取支付信息 ----
	 *
	 * @ApiSummary  (flbooth 获取支付信息)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 订单ID
	 */
	public function getPay()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$id = $this->request->post('order_id');
			$id ? $id : ($this->error(__('非法请求')));
			$order_type = $this->request->post('order_type');
			// 1.0.8升级 营销活动-拼团
			if($order_type == 'groups'){
				$model_order = model('app\api\model\flbooth\groups\Order');
			}else{
				$model_order = model('app\api\model\flbooth\Order');
			}
			// 判断权限
			$orderState = $model_order
				->where(['id' => $id, 'user_id' => $this->auth->id])
				->find();
			$orderState['state'] != 1 ? ($this->error(__('订单异常'))):'';
			// 获取支付信息 1.1.2升级
			$pay = model('app\api\model\flbooth\Pay')
				->where(['order_id' => $id, 'type' => $order_type == 'groups' ? 'groups' : 'goods'])
				->field('id,order_id,order_no,pay_no,price')
				->find();
			$pay['order_type'] = $order_type ? $order_type : 'goods';
			// 传递Token
			$pay['token'] = self::creatToken();
			$this->success('ok', $pay);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 支付订单
	 *
	 * @ApiSummary  (flbooth 支付订单)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $order_id 订单ID
	 * @param string $type 支付类型
	 */
	public function payment()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $order_id = $this->request->post('order_id/a');
			$order_id ? $order_id : ($this->error(__('非法请求')));
			$order_type = $this->request->post('order_type');
			$type = $this->request->post('type');
			$method = $this->request->post('method');
			$code = $this->request->post('code');
			$token = $this->request->post('token');
			// 验证Token
			if($token){
				if(!self::checkToken($token)){
					$this->error(__('页面安全令牌已过期！请重返此页'));
				}
			}else{
				$this->error(__('非法提交，未传入Token'));
			}
			$user_id = $this->auth->id;
			$type ? $type : ($this->error(__('未选择支付类型')));
			
			// 1.0.8升级 营销活动-拼团
			if($order_type == 'groups'){
				$model_order = model('app\api\model\flbooth\groups\Order');
				$model_order_goods = model('app\api\model\flbooth\groups\OrderGoods');
				$model_goods = model('app\api\model\flbooth\groups\Goods');
				$model_goods_sku = model('app\api\model\flbooth\groups\GoodsSku');
			}else{
				$model_order = model('app\api\model\flbooth\Order');
				$model_order_goods = model('app\api\model\flbooth\OrderGoods');
				$model_goods = model('app\api\model\flbooth\Goods');
				$model_goods_sku = model('app\api\model\flbooth\GoodsSku');
			}
			
			// 判断权限
			$order = $model_order
                ->where('id', 'in', $order_id)
                ->where('user_id', $user_id)
				->select();
			if(!$order){
			    $this->error(__('没有找到任何要支付的订单'));
			}
			foreach($order as $item){
				if($item['state'] != 1){
				    $this->error(__('订单已支付，或网络繁忙'));
				}
				// 1.0.5升级 修复付款减库存
				foreach($model_order_goods->where('order_id', $item['id'])->select() as $data){
					$redis = Common::redis();
					// 获取sku
					$sku = $model_goods_sku->get($data['goods_sku_id']);
					// 1.1.2升级
					$sku_key = ($order_type == 'groups' ? 'groups':'goods').'_'.$sku['goods_id'].'_'.$sku['id'];
					// 查询商品
					$goods = $model_goods
						->where(['id' => $data['goods_id'], 'stock' => 'payment'])
						->find();
					// 库存计算方式:porder=下单减库存,payment=付款减库存 1.0.8升级
					if($goods) {
						// 1.1.2升级
						if($data['number'] > $redis->llen("{$sku_key}")){
							$this->error("系统繁忙，请稍后抢购！");
						}else{
							for ($i = 0; $i < $data['number']; $i ++) {
								$redis->rpop("{$sku_key}");
							}
							$sku->setDec('stock', $data['number']); // 1.0.3升级
						}
					}
				}
			}
			// 调用支付
			$wanlPay = new WanlPay($type, $method, $code);
			$data = $wanlPay->pay($order_id, $order_type);
			if($data['code'] == 200){
			    $this->success('ok', $data['data']);
			}else{
			    $this->error($data['msg']);
			}
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 用户充值
	 */
	public function recharge()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
			$money = $this->request->post('money');
			$type = $this->request->post('type');
			$method = $this->request->post('method');
			$code = $this->request->post('code');
			$user_id = $this->auth->id;
			$type ? $type : ($this->error(__('未选择支付类型')));
			$money ? $money : ($this->error(__('为输入充值金额')));
			// 调用支付
			$wanlPay = new WanlPay($type, $method, $code);
			$data = $wanlPay->recharge($money);
			if($data['code'] == 200){
			    $this->success($data['msg'], $data['data']);
			}else{
			    $this->error($data['msg']);
			}
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 用户提现账户
	 */
	public function getPayAccount()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $row = model('app\api\model\flbooth\PayAccount')
		        ->where(['user_id' => $this->auth->id])
		        ->order('created desc')
		        ->select();
		    $this->success('ok', $row);
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 新增提现账户
	 */
	public function addPayAccount()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
		    $post = $this->request->post();
		    $post['user_id'] = $this->auth->id;
            $row = model('app\api\model\flbooth\PayAccount')->allowField(true)->save($post);
		    if($row){
		        $this->success('ok', $row);
		    }else{
		        $this->error(__('新增失败'));
		    }
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 删除提现账户
	 */
	public function delPayAccount($ids = '')
	{	
		$row = model('app\api\model\flbooth\PayAccount')
			->where('id', 'in', $ids)
			->where(['user_id' => $this->auth->id])
			->delete();
		if($row){
		    $this->success('ok', $row);
		}else{
		    $this->error(__('删除失败'));
		}
	}
	
	/**
	 * 初始化提现
	 */
	public function initialWithdraw()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
			$config = get_addon_config('flbooth');
		    $bank = model('app\api\model\flbooth\PayAccount')
		        ->where(['user_id' => $this->auth->id])
		        ->order('created desc')
		        ->find();
		    $this->success('ok', [
		        'money' => $this->auth->money,
				'servicefee' => $config['withdraw']['servicefee'],
		        'bank' => $bank
		    ]);
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 用户提现
	 */
	public function withdraw()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
	    if ($this->request->isPost()) {
	        // 金额
			$money = $this->request->post('money');
            // 账户
            $account_id = $this->request->post('account_id');
            if ($money <= 0) {
                $this->error('提现金额不正确');
            }
            if ($money > $this->auth->money) {
                $this->error('提现金额超出可提现额度');
            }
            if (!$account_id) {
                $this->error("提现账户不能为空");
            }
            // 查询提现账户
            $account = \app\api\model\flbooth\PayAccount::where(['id' => $account_id, 'user_id' => $this->auth->id])->find();
            if (!$account) {
                $this->error("提现账户不存在");
            }
            $config = get_addon_config('flbooth');
            if ($config['withdraw']['state'] == 'N'){
                $this->error("系统该关闭提现功能，请联系平台客服");
            }
            if (isset($config['withdraw']['minmoney']) && $money < $config['withdraw']['minmoney']) {
                $this->error('提现金额不能低于' . $config['withdraw']['minmoney'] . '元');
            }
            if ($config['withdraw']['monthlimit']) {
                $count = \app\api\model\flbooth\Withdraw::where('user_id', $this->auth->id)->whereTime('created', 'month')->count();
                if ($count >= $config['withdraw']['monthlimit']) {
                    $this->error("已达到本月最大可提现次数");
                }
            }
			// 计算提现手续费
			if($config['withdraw']['servicefee'] && $config['withdraw']['servicefee'] > 0){
				$servicefee = number_format($money * $config['withdraw']['servicefee'] / 1000, 2);
				$handingmoney = $money - number_format($money * $config['withdraw']['servicefee'] / 1000, 2);
			}else{
				$servicefee = 0;
				$handingmoney = $money;
			}
            Db::startTrans();
            try {
                $data = [
                    'user_id' => $this->auth->id,
                    'money'   => $handingmoney,
					'handingfee' => $servicefee, // 手续费
                    'type'    => $account['bankCode'],
                    'account' => $account['cardCode'],
					'orderid' => date("Ymdhis") . sprintf("%08d", $this->auth->id) . mt_rand(1000, 9999)
                ];
                $withdraw = \app\api\model\flbooth\Withdraw::create($data);
				$pay = new WanlPay;
				$pay->money(-$money, $this->auth->id, '申请提现', 'withdraw', $withdraw['id']);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
			$this->success('提现申请成功！请等待后台审核', $this->auth->money);
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 获取提现日志
	 */
	public function withdrawLog()
	{
	    //设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$list = model('app\api\model\flbooth\Withdraw')
				->where('user_id', $this->auth->id)
				->order('created desc')
				->paginate();
			$this->success('ok',$list);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 获取支付日志
	 */
	public function moneyLog()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$list = model('app\common\model\MoneyLog')
				->where('user_id', $this->auth->id)
				->order('created desc')
				->paginate();
			$this->success('ok',$list);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 获取支付详情
	 */
	public function details($id = null, $type = null)
	{
		if($type == 'pay'){
			$field = 'id,shop_id,created,paymenttime';
			$order = model('app\api\model\flbooth\Order')
				->where('order_no', 'in', $id)
				->where('user_id', $this->auth->id)
				->field($field)
				->select();
			//1.0.5升级 临时修改,后续升级版本重构
			if(!$order){
				$shop = model('app\api\model\flbooth\Shop')->get(['user_id' => $this->auth->id]);
				$order = model('app\api\model\flbooth\Order')
					->where('order_no', 'in', $id)
					->where('shop_id', $shop['id'])
					->field($field)
					->select();
				if(!$order) $this->error(__('订单异常'));
			}
			foreach($order as $vo){
				// 1.1.2升级
				$vo['pay'] = model('app\api\model\flbooth\Pay')
					->where(['order_id' => $vo['id'], 'type' => 'goods'])
					->field('price,pay_no,order_no,order_price,trade_no,actual_payment,freight_price,discount_price,total_amount')
					->find();
				$vo->shop->visible(['shopname']);
				$vo->goods = model('app\api\model\flbooth\OrderGoods')
					->where(['order_id' => $vo['id']])
					->field('id,title,difference,image,price,number')
					->select();
			}
			$this->success('ok', $order);
		}else if($type == 'groups'){
			$field = 'id,shop_id,created,paymenttime';
			$order = model('app\api\model\flbooth\groups\Order')
				->where('order_no', 'in', $id)
				->where('user_id', $this->auth->id)
				->field($field)
				->select();
			//1.0.5升级 临时修改,后续升级版本重构
			if(!$order){
				$shop = model('app\api\model\flbooth\Shop')->get(['user_id' => $this->auth->id]);
				$order = model('app\api\model\flbooth\groups\Order')
					->where('order_no', 'in', $id)
					->where('shop_id', $shop['id'])
					->field($field)
					->select();
				if(!$order) $this->error(__('订单异常'));
			}
			foreach($order as $vo){
				// 1.1.2升级
				$vo['pay'] = model('app\api\model\flbooth\Pay')
					->where(['order_id' => $vo['id'], 'type' => 'groups'])
					->field('price,pay_no,order_no,order_price,trade_no,actual_payment,freight_price,discount_price,total_amount')
					->find();
				$vo->shop->visible(['shopname']);
				$vo->goods = model('app\api\model\flbooth\groups\OrderGoods')
					->where(['order_id' => $vo['id']])
					->field('id,title,difference,image,price,number')
					->select();
			}
			$this->success('ok', $order);
		}else if($type == 'recharge' || $type == 'withdraw'){ // 用户充值
			if($type == 'recharge'){
				$model = model('app\api\model\flbooth\RechargeOrder');
				$field = 'id,paytype,orderid,memo';
			}else{
				$model = model('app\api\model\flbooth\Withdraw');
				$field = 'id,money,handingfee,status,type,account,orderid,memo,transfertime';
			}
			$row = $model
				->where(['id' => $id, 'user_id' => $this->auth->id])
				->field($field)
				->find();
			$this->success('ok', $row);
		}else if($type == 'refund'){
			$order = model('app\api\model\flbooth\Order')
				->where('order_no', $id)
				->where('user_id', $this->auth->id)
				->field('id,shop_id,order_no,created,paymenttime')
				->find();
			if(!$order){
				$this->error(__('订单异常'));
			}
			$order->shop->visible(['shopname']);
			$order['refund'] = model('app\api\model\flbooth\Refund')
				->where(['order_id' => $order['id'], 'user_id' => $this->auth->id])
				->field('id,price,type,reason,created,completetime')
				->find();
			$this->success('ok', $order);
		}else{ // 系统
			$this->success('ok');
		}
	}
	
	/**
	 * 获取余额
	 */
	public function getBalance()
	{
		$this->success('ok', $this->auth->money);
	}
	
	/**
	 * 创建Token
	 */
	private function creatToken() {
		$code = chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE)) . chr(mt_rand(0xB0, 0xF7)) . chr(mt_rand(0xA1, 0xFE));
		$key = "flbooth.COM";
		$code = md5($key . substr(md5($code), 8, 10));
		Cache::set('orderToken', $code, 180);
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