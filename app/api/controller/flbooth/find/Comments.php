<?php
namespace app\api\controller\flbooth\find;

use app\common\controller\Api;
use addons\flbooth\library\WeixinSdk\Security;
use fast\Tree;

/**
 * flbooth 发现接口
 */
class Comments extends Api
{
    protected $noNeedLogin = [''];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
	    $this->model = new \app\api\model\flbooth\find\Comments;
	}
	
	/**
	 * 获取发现评论列表
	 *
	 * @ApiSummary  (flbooth 关注获取发现评论列表)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $id 发现ID
	 */
	public function getList($id = null)
	{
		$list = $this->model
			->where(['find_id' => $id])
			->select();
		foreach ($list as $row) {
			$row->user->visible(['id','avatar','nickname']);
			// 是否可以删除评论
			$row->owner = $row['user_id'] === $this->auth->id ? true : false; 
			// 是否已经点赞
			$row->hasLike = model('app\api\model\flbooth\find\CommentsLike')
				->where([
					'comments_id' => $row['id'], 
					'user_id' => $this->auth->id
				])
				->count() === 0 ? false : true;
		}
		$tree = Tree::instance()->init($list);
		$this->success('ok', [
			'list' => $tree->getTreeArray(0),
			'count' => count($list)
		]);
	}
	
	/**
	 * 发现页发表评论
	 *
	 * @ApiSummary  (flbooth 发现页发表评论)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 发现ID
	 */
	public function addData()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$find_id = $this->request->post('find_id');
			$find_id ? $find_id : ($this->error(__('非法请求')));
			$content = $this->request->post('content');
			$pid = $this->request->post('pid');
			// 内容审核
			$config = get_addon_config('flbooth');
			$security = new Security($config['mp_weixin']['appid'], $config['mp_weixin']['appsecret']);
			$check = $security->check('msg_sec_check', ['content' => $content]);
			if($check['code'] !== 0){
				if($check['code'] === 87014){
					$this->error(__('评论包含敏感词汇'));
				}else{
					$this->error(__($check['msg']));
				}
			}
			$find = model('app\api\model\flbooth\find\Find')
				->where(['id' => $find_id])
				->find();
			if(!$find){
				$this->error(__('作品不存在无法评论'));
			}
			$row = $this->model;
			$row->data([
				'pid' => $pid ? $pid : 0,
			    'find_id'  => $find['id'],
				'user_id'  => $this->auth->id,
				'shop_id'  => $find['shop_id'],
			    'content' => $content
			]);
			if($row->save()){
				$find->setInc('comments');
				$this->success('ok');
			}else{
				$this->error(__('评论失败'));
			}
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 点赞发现评论列表
	 *
	 * @ApiSummary  (flbooth 发现点赞发现评论列表)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $id 评论ID
	 */
	public function likeData($id = 0)
	{
		$model = model('app\api\model\flbooth\find\CommentsLike');
		if($model->where(['comments_id' => $id])->count() === 0){
			$model->save([
				'comments_id' => $id,
				'user_id' => $this->auth->id
			]);
			$this->model->where(['id' => $id])->setInc('like');
			$this->success('返回成功', ['data' => true]);
		}else{
			$model->where([
				'comments_id' => $id,
				'user_id' => $this->auth->id
			])->delete();
			$this->model->where(['id' => $id])->setDec('like');
			$this->success('返回成功', ['data' => false]);
		}
	}
	
	/**
	 * 删除评论
	 *
	 * @ApiSummary  (flbooth 发现删除评论)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 评论ID
	 */
	public function delData()
	{
	    if ($this->request->isPost()) {
			$id = $this->request->post("id");
			$find_id = $this->request->post("find_id");
			if (!$id || !$find_id) {
				$this->error(__('未传入作品或评论ID'));
			}
			$ids = array_column(Tree::instance()->init($this->model
				->where('find_id', $find_id)
				->select())->getChildren($id, true), 'id');
			$row = $this->model
				->where([
					'id' => $id,
					'find_id' => $find_id,
					'user_id' => $this->auth->id
				])->find();
			if ($row) {
				$destroy = $this->model->destroy($ids);
				if($destroy){
					model('app\api\model\flbooth\find\Find')
						->where(['id' => $find_id])
						->setDec('comments', count($ids));
					$this->success('返回成功', ['count' => $this->model->where(['id' => $find_id])->count()]);
				}else{
					$this->error(__('服务器繁忙，删除失败'));
				}
			}else{
				$this->error(__('前端异常，后台已删除请重返此页面查看'));
			}
	    }else{
			$this->error(__("Invalid parameters"));
		}
	}
    
}