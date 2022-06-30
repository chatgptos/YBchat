<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use think\Db;
/**
 * flbooth 消息接口
 */
class Notice extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    
    public function _initialize()
    {
        parent::_initialize();
    }
	
	
	/**
	 * 获取消息列表
	 *
	 * @ApiSummary  (flbooth 消息接口获取消息列表)
	 * @ApiMethod   (GET)
	 * 2020年5月12日23:25:40
	 *
	 * @param string $type 消息类型
	 */
	public function getNoticeList($type)
	{
		$list = model('app\api\model\flbooth\Notice')
			->where(['user_id' => $this->auth->id, 'type' => $type])
			->where('created','> time',date('Y-m-d',time()-2592000))
			->order('created desc')
			->paginate()
			->each(function($order, $key){
				// 类型:order=订单,refund=退款,groupsorder=营销活动-拼团订单,groupsrefund=营销活动-拼团退款,live=直播,goods=商品
				switch ($order['modules'])
				{
					// 订单模块
					case 'order':
						$order['url'] = '/pages/user/order/details?id='.$order['modules_id'];
					break;  
					// 退款模块
					case 'refund':
						$order['url'] = '/pages/user/refund/details?id='.$order['modules_id'];
					break;
					// 营销活动-拼团订单模块
					case 'groupsorder':
						$order['url'] = '/pages/apps/groups/order/details?id='.$order['modules_id'];
					break;  
					// 营销活动-拼团退款模块
					case 'groupsrefund':
						$order['url'] = '/pages/user/refund/details?id='.$order['modules_id'];
					break;
					// 直播模块
					case 'live':
						$order['url'] = '/pages/flbooth/no_network?id='.$order['modules_id'];
					break;
					// 商品模块
					case 'goods':
						$order['url'] = '/pages/product/goods?id='.$order['modules_id'];
					break;
					// 后续版本继续更新更多提示内容，和商家推送
				}
				return $order;
			});
		$list?($this->success('ok',$list)):($this->error(__('网络繁忙')));
	}
    
}
