<?php
namespace app\api\controller\flbooth\groups;

use app\common\controller\Api;
use think\Db;

/**
 * flbooth 营销活动
 */
class Team extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
	
	/**
	 * 获取营销活动-拼团详情
	 *
	 * @ApiSummary  (flbooth 营销活动-拼团应用获取营销活动-拼团详情)
	 * @ApiMethod   (GET)
	 * 2021年5月26日04:56:03
	 *
	 * @param string $state 状态
	 */
	public function getGroupsDetails()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->request("id"); 
		$id ? $id : ($this->error(__('非正常访问未传递正确ID')));
		$chiefOrderGoods = 0; // 团长订单号
		$row = model('app\api\model\flbooth\groups\Groups')
			->where('group_no', $id)
			->field('id,group_no,user_id,group_type,people_num,join_num,state,validitytime')
			->find();
		if(!$row){
			$this->error(__('未找到任何营销活动-拼团'));
		}
		$row->team = model('app\api\model\flbooth\groups\Team')
			->field('id,user_id,username,nickname,avatar,order_goods_id,created')
			->with(['user'])
			->where('group_no', $id)
			->order('created', 'asc')
			->select();
			
		foreach ($row->team as $team) {
			// 查询团长订单号
			if($team['user_id'] === $row['user_id']){
				$chiefOrderGoods = $team['order_goods_id'];
			}
			$team->getRelation('user')->visible(['avatar']);
		}
		$row->user->visible(['username','nickname','avatar']);
		$row->orderGoods = model('app\api\model\flbooth\groups\OrderGoods')
			->where(['id' => $chiefOrderGoods])
			->field('id,goods_id,title,image,difference,price,group_price,market_price')
			->find();
		if($row)
			$this->success('ok', $row);
		else
			$this->error(__('网络繁忙'));
	}
}