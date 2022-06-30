<?php
namespace app\api\controller\flbooth\find;

use app\common\controller\Api;
use fast\Random;

/**
 * flbooth 发现中心接口
 */
class User extends Api
{
    protected $noNeedLogin = ['getList', 'userInfo'];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
		$this->model = model('app\api\model\flbooth\find\User');
	}
	
	/**
	 * 获取关注列表
	 *
	 * @ApiSummary  (flbooth 发现接口获取关注列表)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function getList($id = 0, $type = 'follow')
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$model = model('app\api\model\flbooth\find\Follow');
		// 判断业务类型
		if($type === 'follow'){
			if($id){
				$where['user_id'] = ['=', $this->getFindUser('user_no', $id)->user_id];
			}else{
				$where['user_id'] = ['=', $this->auth->id];
			}
		}else if($type === 'fans'){
			if($id){
				$where['user_no'] = ['=', $id];
			}else{
				$where['user_no'] = ['=', $this->getFindUser('user_id', $this->auth->id)->user_no];
			}
		}
		$list = $model
			->where($where)
			->field('id, user_id, user_no')
			->paginate();
		foreach ($list as $row) {
			if($type === 'follow'){
				$row['user'] = $this->getFindUser('user_no', $row['user_no'])
					->user->visible(['id', 'avatar', 'nickname', 'username', 'bio']);
				$row['isFollow'] = $model
					->where([
						'user_no' => $row['user_no'], 
						'user_id' => $this->auth->id
					])
					->count();
				$row['shop'] = model('app\api\model\flbooth\Shop')
					->where(['user_id' => $row['user']['id']])
					->field('id, user_id, avatar, shopname, bio, isself')
					->find();
			}else if($type === 'fans'){
				$find = $this->getFindUser('user_id', $row['user_id']);
				$row['user'] = $find->user->visible(['id','avatar', 'nickname', 'username', 'bio']);
				$row['user_no'] = $find->user_no;
				$row['isFollow'] = $model
					->where([
						'user_no' => $find->user_no, 
						'user_id' => $this->auth->id
					])
					->count();
				$row['shop'] = model('app\api\model\flbooth\Shop')
					->where(['user_id' => $row['user']['id']])
					->field('id, user_id, avatar, shopname, bio, isself')
					->find();
			}
		}
		$this->success('返回成功', $list);
	}
	
	/**
	 * 获取关注商家列表
	 *
	 * @ApiSummary  (flbooth 发现接口获取关注商家列表)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function getShopList()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$model = model('app\api\model\flbooth\find\Follow');
		$shopFindIds = [];
		foreach ($model->where(['user_id' => $this->auth->id])->field('id, user_no')->select() as $row) {
			$shop = model('app\api\model\flbooth\Shop')
				->where(['user_id' => $this->getFindUser('user_no', $row['user_no'])->user_id])
				->find();
			if($shop){
				$shopFindIds = $row['id'];
			}
		}
		$list = $model
			->where('id', 'in', $shopFindIds)
			->field('id, user_no')
			->paginate();
		foreach ($list as $row) {
			$row['shop'] = model('app\api\model\flbooth\Shop')
				->where(['user_id' => $this->getFindUser('user_no', $row['user_no'])->user_id])
				->field('id, user_id, shopname, avatar, state, level, city, isself')
				->find();
			$row['isFollow'] = 1;
		}
		$this->success('返回成功', $list);
	}
	
	/**
	 * 获取发现用户信息
	 *
	 * @ApiSummary  (flbooth 发现接口获取发现用户信息)
	 * @ApiMethod   (POST)
	 * 
	 */
	public function userInfo()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $id = $this->request->post('id');
			if ($id) {
				$where['user_no'] = $id;
			} else {
				$where['user_id'] = $this->auth->id;
			}
			// 获取社区会员详情
			$row = $this->model->where($where)->find();
			if (!$row) {
				$row = $id ? 
					$this->error(__('未找到此用户')) : 
					$this->model->get( $this->addFindUser()->id );
			}
			$row->isFollow = model('app\api\model\flbooth\find\Follow')
				->where(['user_no' => $row['user_no'], 'user_id' => $this->auth->id])
				->count();
			$row->user->visible(['id','avatar','nickname','username','bio']);
			$row->shop = model('app\api\model\flbooth\Shop')
				->where(['user_id' => $row->user->id])
				->field('id, user_id, shopname, avatar, state, level, city, isself')
				->find();
			$config = get_addon_config('flbooth');
			$video = true;
			$live = true;
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
			$row->isVideo = $video;
			$row->isLive = $live;
			$this->success('返回成功', $row);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 喜欢或取消发现
	 *
	 * @ApiSummary  (flbooth 发现接口喜欢或取消发现)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 发现ID
	 */
	public function setFindUser()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $id = $this->request->post('id');
		    $type = $this->request->post('type');
		    if (!$id) {
		        $this->error(__('未传入正确的FIND_ID'));
		    }
		    // 判断业务类型
			switch ($type) {
				case 'follow':
					$model = model('app\api\model\flbooth\find\Follow');
					$where_type = 'user_no';
					$my_type = 'fans';
					break; 
				case 'likes':
					$model = model('app\api\model\flbooth\find\Like');
					$where_type = 'find_id';
					$my_type = 'praised';
					break;
				default:
					$this->error(__('未传入正确的FIND_TYPE'));
			}
		    // 查询我的创作中心
		    $myUser = $this->model->where(['user_id' => $this->auth->id])->find();
		    if (!$myUser) {
		        $myUser = $this->model->get($this->addFindUser()->id);
		    }
		    // 判断是否已经关注
		    $where[$where_type] = $id;
		    $where['user_id'] = $this->auth->id;
			
		    if ($model->where($where)->count() === 0) {
		        $model->save($where);
				if($type === 'likes'){
					$like = model('app\api\model\flbooth\find\Find')->get($id);
					$like->setInc($type);
					$this->model->where('user_id', $like['user_id'])->setInc($my_type);
				}else{
					$this->model->where($where_type, $id)->setInc($my_type);
				}
		        $myUser->setInc($type);
		        $this->success('返回成功', ['data' => 1]);
		    } else {
		        $model->where($where)->delete();
				if($type === 'likes'){
					$like = model('app\api\model\flbooth\find\Find')->get($id);
					$like->setDec($type);
					$this->model->where('user_id', $like['user_id'])->setDec($my_type);
				}else{
					$this->model->where($where_type, $id)->setDec($my_type);
				}
		        $myUser->setDec($type);
		        $this->success('返回成功', ['data' => 0]);
		    }
		}
		$this->error(__('非正常访问'));
	}
	
	/**
	 * 获取主题
	 *
	 * @ApiSummary  (flbooth 发现接口获取主题)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function getTheme()
	{
		$row = $this->model
			->where(['user_id' => $this->auth->id])
			->field('user_no, color, image')
			->find();
		$row['list'] = model('app\api\model\flbooth\Theme')
			->field('id, color, image, name')
			->select();
		$this->success('返回成功', $row);
	}
	
	/**
	 * 修改创作中心主题
	 *
	 * @ApiSummary  (flbooth 发现接口修改创作中心主题)
	 * @ApiMethod   (POST)
	 * 
	 */
	public function editTheme()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $url = $this->request->post('url');
		    $type = $this->request->post('type');
		    // 上传图片
		    $update['image'] = $url;
		    if ($type && $type === 'upload') {
		        $imgUrl = cdnurl($url, true);
		        $imageInfo = getimagesize($imgUrl);
		        //图片类型
		        $imgType = strtolower(substr(image_type_to_extension($imageInfo[2]) , 1));
		        //对应函数
		        $imageFun = 'imagecreatefrom' . ($imgType == 'jpg' ? 'jpeg' : $imgType);
		        $i = $imageFun($imgUrl);
		        //循环色值
		        $rColorNum = $gColorNum = $bColorNum = $total = 0;
		        for ($x = 0; $x < imagesx($i); $x++) {
		            for ($y = 0; $y < imagesy($i); $y++) {
		                $rgb = imagecolorat($i, $x, $y);
		                //三通道
		                $r = ($rgb >> 16) & 0xFF;
		                $g = ($rgb >> 8) & 0xFF;
		                $b = $rgb & 0xFF;
		                $rColorNum+= $r;
		                $gColorNum+= $g;
		                $bColorNum+= $b;
		                $total++;
		            }
		        }
		        $r = round($rColorNum / $total);
		        $g = round($gColorNum / $total);
		        $b = round($bColorNum / $total);
		        $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
		        $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
		        $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));
		        $color = (strlen($r) < 2 ? '0' : '') . $r;
		        $color.= (strlen($g) < 2 ? '0' : '') . $g;
		        $color.= (strlen($b) < 2 ? '0' : '') . $b;
		        $update['color'] = '#' . $color;
		    } else {
		        $update['color'] = $this->request->post('color');
		    }
			if($this->model->where(['user_id' => $this->auth->id])->update($update)){
				$this->success('返回成功', $update);
			}else{
				$this->error(__('修改主题失败'));
			}
		}
		$this->error(__('非法请求'));
	}
	
	
	
    /**
     * 新建用户 内部方法
     */
    private function addFindUser()
	{
		// 新建用户并查询
		$find_user = $this->model;
		$find_user->user_id = $this->auth->id;
		$find_user->user_no = Random::nozero(9);
		$find_user->save();
		return $find_user;
	}
	
	/**
	 * 查询用户 内部方法
	 */
	private function getFindUser($name = null, $id = null)
	{
		return $this->model->where($name, '=', $id)->find();
	}
}
