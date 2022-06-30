<?php
namespace app\api\controller\flbooth\find;

use app\common\controller\Api;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use fast\Random;
use addons\flbooth\library\WeixinSdk\Security;
use addons\flbooth\library\AliyunSdk\Video;

/**
 * flbooth 发现接口
 */
class Find extends Api
{
    protected $noNeedLogin = ['getInit', 'getDetails', 'getList'];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
	    $this->model = new \app\api\model\flbooth\find\Find;
	}
	
	/**
	 * 获取发现顶部菜单
	 *
	 * @ApiSummary  (flbooth 发现接口获取发现页、店铺、创作中心数据)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function getInit()
	{
		$video = true;
		$live = true;
		$shop = model('app\api\model\flbooth\Shop')->get(['user_id'=> $this->auth->id]);
		$row['id'] = 'find';
		$row['list'] = [['name' => '发现', 'type' => 'find']];
		$config = get_addon_config('flbooth');
		// 判断客户端类型
		switch ($this->request->server('HTTP_APP_CLIENT')){
			case 'app-flbooth':
				$video = isset($config['find']['app_switch']['video']);
				$live = isset($config['find']['app_switch']['live']);
				break;  
			case 'h5-flbooth':
				$video = isset($config['find']['h5_switch']['video']);
				$live = isset($config['find']['h5_switch']['live']);
				break;  
			case 'mp-flbooth':
				$video = isset($config['find']['mp_switch']['video']);
				$live = isset($config['find']['mp_switch']['live']);
				break;
			case 'wechat-flbooth':
				$video = isset($config['find']['wechat_switch']['video']);
				$live = isset($config['find']['wechat_switch']['live']);
				break;  
		}
		if($video){
			$row['id'] = 'video';
			$row['list'][] = ['name' => '视频','type' => 'video'];
		}
		$row['video'] = $video;
		$row['live'] = $live;
		$row['shop'] = $shop ? true : false;
		$this->success('ok', $row);
	}
	
	/**
	 * 获取发现数据
	 *
	 * @ApiSummary  (flbooth 发现接口获取发现页、店铺、创作中心数据)
	 * @ApiMethod   (GET)
	 * follow、空 主栏目、video
	 */
	public function getList($type = null, $user_no = null, $search = null, $find_id = null)
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$where = [];
		$client = [];
		$exclude = [];
		$config = get_addon_config('flbooth');
		// 判断客户端类型
		switch ($this->request->server('HTTP_APP_CLIENT')){
			case 'app-flbooth':
				$client['type'] = ['in', array_keys($config['find']['app_switch'])];
				break;  
			case 'h5-flbooth':
				$client['type'] = ['in', array_keys($config['find']['h5_switch'])];
				break;  
			case 'mp-flbooth':
				$client['type'] = ['in', array_keys($config['find']['mp_switch'])];
				break;
			case 'wechat-flbooth':
				$client['type'] = ['in', array_keys($config['find']['wechat_switch'])];
				break;  
		}
		// 查询状态
		if($type === 'myfind'){
			$exclude['user_id'] = ['eq', $this->auth->id];
		}else{
			$exclude['state'] = ['eq', 'normal'];
		}
		// 创作中心的喜欢
		if($type === 'likes'){
			if(!$user_no){
				$this->error(__('未传入正确的USER_NO'));
			}
			$like = model('app\api\model\flbooth\find\Like')
				->where('user_id', self::getFindUser('user_no', $user_no)->user_id)
				->select();
			$where['id'] = ['in', array_column($like, 'find_id')];
		// 主页关注
		}else if($type === 'find'){
			$where['type'] = ['neq', 'video'];
		// 主页视频
		}else if($type === 'video'){
			$where['type'] = ['eq', $type];
		// 主页关注
		}else if($type === 'follow'){
			$follow = model('app\api\model\flbooth\find\Follow')
				->where('user_id', $this->auth->id)
				->select();
			$where['user_no'] = ['in', array_column($follow, 'user_no')];
		}
		// 查询用户
		if($user_no){
			if($find_id){
				$where['id'] = ['neq', $find_id];
				$where['type'] = ['neq', 'live'];
			}
			$where['user_no'] = ['eq', $user_no];
		}else{
			if($find_id){
				$where['id'] = ['eq', $find_id];
			}
		}
		// 发现搜索列表判断是否搜索
		if($search){
			if($type === 'user'){
				$where['nickname'] = ['like', "%$search%"];
			}else{
				$where['content'] = ['like', "%$search%"];
			}
		}
		// 发现搜索列表 查询用户
		if($type === 'user'){
			$user = model('app\api\model\User')
				->where($where)
				->select();
			$list = model('app\api\model\flbooth\find\User')
				->where('user_id', 'in', array_column($user, 'id'))
				->field('id, user_id, user_no, fans, praised')
				->order('created DESC')
				->paginate();
			foreach ($list as $row) {
				$row->isFollow = model('app\api\model\flbooth\find\Follow')
					->where(['user_no' => $row['user_no'],'user_id' => $this->auth->id])
					->count();
				$row->user->visible(['id','username','nickname','avatar','bio']);
				$row->shop = model('app\api\model\flbooth\Shop')
					->where('user_id', $row['user_id'])
					->field('id, user_id, avatar, shopname, bio, isself')
					->find();
			}
		}else{
			$list = $this->model
				->where($exclude)
				->where($client)
				->where($where)
				->order('created DESC')
				->paginate();
			foreach ($list as $row) {
				if(!$type || $type === 'follow' || $type === 'find' || $row['type'] === 'video'){
					if(!$user_no){
						$row->shop = null;
						$shop = model('app\api\model\flbooth\Shop')
							->where(['user_id' => $row['user_id']])
							->field('id, user_id, avatar, shopname, isself')
							->find();
						if($shop){
							$row->shop = $shop;
							$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $shop['id'], 'state' => 1])->field('id')->find();
							$row->newGoods = model('app\api\model\flbooth\Goods')
								->where('shop_id', $shop['id'])
								->whereTime('created', 'w') // 查询本周
								->count();
						}else{
							$row->user->visible(['id','avatar','username','nickname']);
						}
					}
					// 查询关注
					$row->isFollow = model('app\api\model\flbooth\find\Follow')
						->where(['user_no' => $row['user_no'],'user_id' => $this->auth->id])
						->count();
					// 关联商品
					$row->goods = model('app\api\model\flbooth\Goods')
						->where('id', 'in', $row['goods_ids'])
						->field('id,title,image,price')
						->select();
				}
				// 查询业务
				if($row['type'] === 'live'){
					$row->live->visible(['id','goods_ids','like','state','views']);
				}else if($row['type'] === 'video'){
					$row->play = false;
					$row->initialTime = 0;
					$row->video;
				}
				$row->isLike = model('app\api\model\flbooth\find\Like')->where(['find_id' => $row['id'], 'user_id' => $this->auth->id])->count();
			}
		}
		
		$this->success('加载完成', $list);
	}
	
	/**
	 * 获取发现详情&列表
	 *
	 * @ApiSummary  (flbooth 关注或取消动态)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 发现ID
	 */
	public function getDetails($id = null)
	{
		$row = $this->model
			->where(['id' => $id, 'state' => 'normal'])
			->find();
		if(!$row){
			$this->error(__('没有找到此作品，可能已经被删除'));
		}
		$row->shop = null;
		$shop = model('app\api\model\flbooth\Shop')
			->where(['user_id' => $row['user_id']])
			->field('id, user_id, avatar, shopname, isself')
			->find();
		if($shop){
			$row->shop = $shop;
			$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $shop['id'], 'state' => 1])->field('id')->find();
			$row->newGoods = model('app\api\model\flbooth\Goods')
				->where('shop_id', $shop['id'])
				->whereTime('created', 'w') // 查询本周
				->count();
		}else{
			// 1.1.4升级
			$row->user->visible(['id','avatar','nickname','username']);
		}
		$row->isFollow = model('app\api\model\flbooth\find\Follow')
			->where(['user_no' => $row['user_no'],'user_id' => $this->auth->id])
			->count();
		$row->isLike = model('app\api\model\flbooth\find\Like')
			->where(['find_id' => $row['id'], 'user_id' => $this->auth->id])
			->count();
		$row->current = 0;
		// 关联商品
		$row->goods = model('app\api\model\flbooth\Goods')
			->where('id','in',$row['goods_ids'])
			->field('id,title,image,price')
			->select();
		// 阅读量 +1
		$row->setInc('views');
		$this->success('ok',$row);
	}
	
	/**
	 * 新增作品
	 *
	 * @ApiSummary  (flbooth 发现新增作品)
	 * @ApiMethod   (POST)
	 * 
	 */
	public function addData()
	{
		//设置过滤方法
		$this->request->filter(['trim']);
		if ($this->request->isPost()) 
		{
			$params = $this->request->post();
			$config = get_addon_config('flbooth');
			// 内容审核
			$security = new Security($config['mp_weixin']['appid'], $config['mp_weixin']['appsecret']);
			$checkText = $security->check('msg_sec_check', [
				'content' => $params['content']
			]);
			if($checkText['code'] !== 0)
			{
				if($checkText['code'] === 87014){
					$this->error(__('风控审核：内容包含敏感词请修改后提交'));
				}else{
					$this->error(__($checkText['msg']));
				}
			}
			// 查询用户
			$params['user_id'] = $this->auth->id;
			$params['user_no'] = self::getFindUser('user_id', $this->auth->id)->user_no;
			// 判断是否审核
			if($config['find']['allExamine_switch'] === 'Y'){
			    if($params['type'] === 'video'){
					$video = model('app\api\model\flbooth\Video')->get(['video_id' => $params['video_id']]);
					$params['images'] = $video ? $video['snapshots'] : '';
				}
				$params['state'] = 'examine';
			}else if($config['find']['personalExamine_switch'] === 'Y'){
				if(model('app\api\model\flbooth\Shop')->where(['user_id' => $this->auth->id])->find()){
					if($params['type'] === 'video'){
						$video = model('app\api\model\flbooth\Video')->get(['video_id' => $params['video_id']]);
						$params['images'] = $video ? $video['snapshots'] : '';
						$params['state'] = $video ? $video['state'] : 'publish';
					}else{
						$params['state'] = 'normal';
					}
				}else{
				    if($params['type'] === 'video'){
						$video = model('app\api\model\flbooth\Video')->get(['video_id' => $params['video_id']]);
						$params['images'] = $video ? $video['snapshots'] : '';
					}
					$params['state'] = 'examine';
				}
			}else{
				if($params['type'] === 'video'){
					$video = model('app\api\model\flbooth\Video')->get(['video_id' => $params['video_id']]);
					$params['images'] = $video ? $video['snapshots'] : '';
					$params['state'] = $video ? $video['state'] : 'publish';
				}else{
					$params['state'] = 'normal';
				}
			}
			$result = false;
			Db::startTrans();
			try {
			    $result = $this->model->allowField(true)->save($params);
				// 提交事务
			    Db::commit();
			} catch (PDOException $e) {
			    Db::rollback();
			    $this->error($e->getMessage());
			} catch (Exception $e) {
			    Db::rollback();
			    $this->error($e->getMessage());
			}
			if ($result !== false) {
			    $this->success();
			} else {
			    $this->error('发布失败');
			}
			$this->success('返回成功', $post);
		}
		$this->error(__('非法请求'));
	}
    
	/**
	 * 删除作品
	 *
	 * @ApiSummary  (flbooth 发现删除作品)
	 * @ApiMethod   (POST)
	 * 
	 */
	public function delData()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			$row = $this->model
				->where(['id' => $id, 'user_id' => $this->auth->id])
				->find();
			if($row){
				// 一并删除云端
				if($row['type'] === 'video'){
					$config = get_addon_config('flbooth');
					$vod = new Video($config['video']['regionId'], $config['video']['accessKeyId'], $config['video']['accessKeySecret']);
					$vodDel = $vod->deleteVideo($row['video_id']);
					if($vodDel){
						$row->delete();
					}
				}else{
					$row->delete();
				}
				$this->success('删除成功');
			}else{
				$this->success('网络异常，未找到此作品');
			}
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 查询用户 内部方法
	 */
	private function getFindUser($name = null, $id = null)
	{
		$row = model('app\api\model\flbooth\find\User')
			->where($name, 'eq', $id)
			->find();
		return $row ? $row : self::addFindUser();
	}
	
	/**
	 * 新建用户 内部方法
	 */
	private function addFindUser()
	{
		// 新建用户并查询
		$find_user = model('app\api\model\flbooth\find\User');
		$find_user->user_id = $this->auth->id;
		$find_user->user_no = Random::nozero(9);
		$find_user->save();
		return $find_user;
	}
}
