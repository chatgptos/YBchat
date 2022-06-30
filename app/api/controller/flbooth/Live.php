<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use fast\Random;
use addons\flbooth\library\AliyunSdk\Alilive;
use addons\flbooth\library\WanlChat\WanlChat;

/**
 * flbooth 发现接口
 */
class Live extends Api
{
    protected $noNeedLogin = ['live'];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
		$this->wanlchat = new WanlChat();
		if(!$this->wanlchat->isWsStart()){
			$this->error(__('即时通讯服务未启动'));
		}
	    $this->model = new \app\api\model\flbooth\Live;
	}
	
	/**
	 * 查询直播间权限 
	 */
	public function getIsLive()
	{
		$row = model('app\api\model\flbooth\Shop')
			->where(['user_id' => $this->auth->id])
			->field('id, avatar, user_id, shopname, islive, isself')
			->find();
		if($row){
			if($row['islive'] === 1){
				$this->success('返回成功', $row);
			}else{
				$this->error('你还没有直播权限，请联系客服申请！');
			}
		}else{
			$this->error(__('您还不是商家，没有直播权限'));
		}
	}
	
	/**
	 * 添加并开始直播
	 *
	 * 接受到直播回调在自动，发布动态
	 */
	public function startLive()
	{
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$post = $this->request->post();
			$user_id = $this->auth->id;
			$shop = model('app\api\model\flbooth\Shop')
				->where(['user_id' => $user_id])
				->find();
			if($shop){
				// 1.1.2升级
				$alilive = new Alilive();
				$alilive = $alilive->auth();
				// 添加直播
				$live = $this->model;
				$live->shop_id = $shop['id'];
				$live->image = $post['image'];
				$live->content = $post['content'];
				$live->goods_ids = $post['goods_ids'];
				// 1.1.2升级
				$live->liveid = $alilive['streamName'];
				$live->liveurl = $alilive['rtmp_play_url'].','.$alilive['hls_play_url'];
				$live->pushurl = $alilive['push_url'];
				$live->save();
				// 创建一个群组将我加入进去
				foreach ($this->wanlchat->getUidToClientId($user_id) as $client_id) {
					$this->wanlchat->joinGroup($client_id, $live->liveid);
				}
				$this->success('返回成功', [
					'id' => $live->id,
					'liveid' => $live->liveid,
					'pushurl' => $live->pushurl
				]);
			}else{
				$this->error(__('您还不是商家，没有直播权限'));
			}
		}
		$this->error(__('非正常访问'));
	}
	
	/**
	 * 结束直播
	 */
	public function endLive($id = null)
	{
		$row = $this->model
			->where(['id' => $id])
			->field('id,shop_id,goods_ids,image,like,views')
			->find();
		$row['goodsnum'] = count(explode(',', $row['goods_ids']));
		$row->shop->visible(['id','avatar','shopname']);
		$this->success('返回成功', $row);
	}
	
	
	/**
	 * 直播播放页
	 */
	public function play($id = null)
	{
		$row = $this->model
			->where(['id' => $id])
			->field('id,liveid,liveurl,recordurl,state,shop_id,goods_ids,image,like')
			->find();
		$user_id = $this->auth->id;
		$follow = 0;
		// 判断是否登录
		if($this->auth->isLogin()){
		    // 获取店铺信息
		    $row->shop->visible(['id','avatar','shopname']);
		    $follow = model('app\api\model\flbooth\find\Follow')->where('user_no', $row->shop['find_user']['user_no'])->count();
			// 浏览 +1
			$this->model->where(['id' => $id])->setInc('views');
			// 创建一个群组将我加入进去
			foreach ($this->wanlchat->getUidToClientId($user_id) as $client_id) {
				$this->wanlchat->joinGroup($client_id, $row['liveid']);
			}
			// 开始广播我进入了直播间
			$this->sendLiveGroup($row['liveid'], ['type' => 'coming']);
		}
		$row->isFollow = $follow == 0 ? false : true;
		// 获取商品信息
		$row->goods = model('app\api\model\flbooth\Goods')
			->where('id', 'in', $row['goods_ids'])
			->field('id,image,title,price')
			->select();
		$this->success('返回成功', $row);
	}
	
	
	/**
	 * 关注商家
	 */
	public function follow($user_no, $group)
	{
		$this->sendLiveGroup($group, [
			'type' => 'follow',
			'text' => '关注了主播'
		]);
		$user_no ? $user_no : ($this->error(__('非正常访问')));
		$model = model('app\api\model\flbooth\find\Follow');
		$where['user_no'] = $user_no;
		$where['user_id'] = $this->auth->id;
		if($model->where($where)->count() == 0){
			$model->save($where);
			model('app\api\model\flbooth\find\User')
				->where('user_no', $user_no)
				->setInc('fans');
			$this->success('返回成功', true);
		}else{
			$model->where($where)->delete();
			model('app\api\model\flbooth\find\User')
				->where('user_no', $user_no)
				->setDec('fans');
			$this->success('返回成功', false);
		}
	}
	
	/**
	 * 直播点赞 +1
	 */
	public function like($id = null)
	{
		$row = $this->model->get($id);
		// 广播群组我点赞了
		$this->sendLiveGroup($row['liveid'], [
			'type' => 'like',
			'text' => '点了一个赞'
		]);
		$row->setInc('like');
		$this->success('返回成功');
	}
	
	/**
	 * 发送直播消息
	 */
	public function send($group, $message)
	{
		$this->sendLiveGroup($group, [
			'type' => 'msg',
			'text' => $message
		]);
	}
	
	/**
	 * 求讲解
	 */
	public function seek($group, $goods_index)
	{	
		$this->sendLiveGroup($group, [
			'type' => 'seek',
			'text' => '求讲解一下'.$goods_index.'号商品'
		]);
	}
	
	/**
	 * 卸载直播页面
	 */
	public function unload($group = null, $type = null)
	{
		// 广播
		if($type == 'rtmp'){
			// 广播关闭直播间
			$this->sendLiveGroup($group, ['type' => 'end']);
		}else{
			// 广播直播间我退出了 -1
			$this->sendLiveGroup($group, ['type' => 'leave']);
		}
		
		// 判断是否登录
		if($this->auth->isLogin()){
			if($type == 'rtmp'){
				// 解散分组
				$this->wanlchat->ungroup($group);
			}else{
				// 退出群组
				foreach ($this->wanlchat->getUidToClientId($this->auth->id) as $client_id) {
					$this->wanlchat->leaveGroup($client_id, $group);
				}
			}
		}
		$this->success('返回成功');
	}
	
	/**
	 * 获取直播商品列表
	 */
	public function goods()
	{
		$shop = model('app\api\model\flbooth\Shop')
			->where(['user_id' => $this->auth->id])
			->find();
			$list = [];
		if($shop){
			$list = model('app\api\model\flbooth\Goods')
				->where(['shop_id' => $shop['id']])
				->field('id,image,title,price')
				->select();
			foreach ($list as $row) {
				$row->choose = false;
			}
		}else{
			$this->error(__('您还不是商家，没有直播权限'));
		}
		$this->success('返回成功', $list);
	}
	
	/**
	 * 主播推送更新商品列表
	 */
	public function cloud($id = 0, $goods_ids = null)
	{
		$row = $this->model->get($id);
		if($row->save(['goods_ids'  => $goods_ids])){
			$goods = model('app\api\model\flbooth\Goods')
				->where('id', 'in', $goods_ids)
				->field('id,title,image,price')
				->select();
			$this->sendLiveGroup($row['liveid'], [
				'type' => 'update',
				'data' => $goods
			]);
		}
		$this->success('推送成功过');
	}
	
	
	/**
	 * 发送直播群组消息
	 * 内部方法
	 */
	private function sendLiveGroup($group, $message)
	{
		// 查询点赞数量
		$like = $this->model
			->where(['liveid' => $group])
			->find();
		$this->wanlchat->sendGroup($group, [
			'type' => 'live',
			'group' => $group,
			'form' => [
				'id' => $this->auth->id,
				'nickname' => $this->auth->nickname,
				// 'avatar' => $this->auth->avatar,
			],
			'message' => $message,
			'online' => $this->wanlchat->getUidCountByGroup($group),
			'like' => $like['like']
		]);
	}
    
}