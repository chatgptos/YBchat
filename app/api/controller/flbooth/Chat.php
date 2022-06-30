<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use addons\flbooth\library\WanlChat\WanlChat;
use think\Session;
use think\Db;
/**
 * flbooth即时通讯接口
 */
class Chat extends Api
{
    protected $noNeedLogin = ['shake','service','hello','state'];
	protected $noNeedRight = ['*'];
    

    public function _initialize()
    {
        parent::_initialize();
		//WanlChat 即时通讯调用
		$this->wanlchat = new WanlChat();
		// 调用配置
		$this->chatConfig = get_addon_config('flbooth');
    }
	
	/**
	 * 查询店铺信息
	 *
	 * @ApiSummary  (flbooth 查询店铺信息)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 店铺ID
	 */
	public function getShopChat()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$id = $this->request->post('id');
			// 1.0.2升级 判断来源
			$type = $this->request->post('type');
			$id ? $id : ($this->error(__('非正常访问')));
			$row = model('app\api\model\flbooth\Shop')
				->where(['id' => $id])
				->field('id,user_id,shopname,avatar,state,level,city,like,isself,created')
				->find();
			if($row){
				if($type == 'chat'){
					if(!$this->wanlchat->isWsStart()){
						$this->error('IM即时通讯异常：请启动IM即时通讯服务');
					}else{
						// 查询是否发送离线消息
						$shop_config = model('app\api\model\flbooth\ShopConfig')
							->where(['shop_id' => $row['id']])
							->find();
						// 查询是否存在聊天记录，如果不存在则发送欢迎消息
						if($shop_config['welcome']){
							$count = model('app\api\model\flbooth\Chat')->where("((form_uid={$row['user_id']} and to_id={$this->auth->id}) or (form_uid={$this->auth->id} and to_id={$row['user_id']})) and type='chat'")->count();
							if($count == 0){
								$this->wanlchat->send($this->auth->id, [
									'id' => $count + 1,
									'to_id' => $this->auth->id,
									'type' => 'chat',
									'form' => [
										'id' => $row['user_id'],
										'shop_id' => $row['id'],
										'name' => $row['shopname'],
										'avatar' => $row['avatar']
									],
									'message' => [
										'type' => 'text',
										'content' => [
											'text' => $shop_config['welcome']
										]
									],
									'created' => time()
								]);
							}
						}
						// 查询商家是否在线
						$row['isOnline'] = $this->wanlchat->isOnline($row['user_id']);
					}
				}
				$this->success('返回成功', $row);
			}
			if($type == 'chat'){
				$this->error(__('对方不是商家，禁止操作'));
			}
		}
		$this->error(__('非正常访问'));
	}
	
    /**
     * 绑定UID
     *
	 * @ApiSummary  (WanlChat 绑定UID)
	 * @ApiMethod   (POST)
	 *
     * @param string $client_id 
     */
    public function shake()
    {
        //设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$client_id = $this->request->post('client_id');
			$client_id?'':($this->error(__('Invalid parameters')));
			// 绑定在线
			if ($this->auth->isLogin()) {
			    $user_id = $this->auth->id;
			    // 查询有没有绑定其他如果有的话全部解绑，退出登录页执行此操作
			    foreach ($this->wanlchat->getUidToClientId($user_id) as $client_id_old) {
			    	$this->wanlchat->unbind($client_id_old, $user_id);
			    }
			    // 重新绑定一个新的
			    $this->wanlchat->bind($client_id, $user_id);
			    // 查询是否有离线消息
				$list = model('app\api\model\flbooth\Chat')
					->where(['to_id' => $user_id, 'online' => 0, 'type' => 'chat'])
					->whereTime('created', 'week')
					->field('id,form_uid,to_id,form,message,type,online,created')
					->select();
				foreach($list as $message){
					$this->wanlchat->send($user_id, $message);
					model('app\api\model\flbooth\Chat')->save(['online' => 1], ['id' => $message['id']]);
				}
				$this->success(__('即时通讯初始化成功'), $client_id);
			}else{
			    // 绑定离线，可能用户在线客服等其他消息通知
			    
			    $this->success(__('即时通讯离线初始化成功'), $client_id);
			}
		}
		$this->error(__('非正常请求'));
    }
    
	/**
	 * 聊天列表
	 *
	 * @ApiSummary  (WanlChat 读取聊天列表)
	 * @ApiMethod   (GET)
	 */
	public function lists()
	{
		$user_id = $this->auth->id;
		$list = [];
		$sub = Db::name('flboothChat')
			->where(['type' => 'chat'])
			->order('created', 'desc')
		    ->field('to_id as uid, message, isread, type, created')
		    ->where('form_uid ='.$user_id)
		    ->union('SELECT form_uid as uid, message, isread, type, created FROM '.config('database.prefix').'booth_chat WHERE to_id = '.$user_id)
			->buildSql();
		$query = Db::table($sub)
			->alias('temp')
			->group('temp.uid')
			->select();
		foreach($query as $row)
		{
			if($row['type'] == 'chat'){ //临时
				$shop = model('app\api\model\flbooth\Shop')
					->where(['user_id' => $row['uid']])
					->find();
				// 统计未读
				$count = model('app\api\model\flbooth\Chat')
					->where(['form_uid' => $shop['user_id'], 'to_id' => $user_id, 'isread' => 0])
					->count();
				
				$content = json_decode($row['message'], true);
				// 转换为文字消息 1.0.2升级
				if($content['type'] == 'img'){
					$msgtext = '[图片消息]';
				}else if($content['type'] == 'voice'){
					$msgtext = '[语音消息]';
				}else if($content['type'] == 'goods'){
					$msgtext = '[商品消息]';
				}else if($content['type'] == 'order'){
					$msgtext = '[订单消息]';
				}else if($content['type'] == 'text'){
					$msgtext = $content['content']['text'];
				}else{
					$msgtext = '[未知消息类型]';
				}
				// 输出
				$list[] = [
					'id' => $shop['id'],
					'user_id' => $shop['user_id'],
					'name' => $shop['shopname'],
					'avatar' => $shop['avatar'],
					'content' => $msgtext,
					'count' => $count,
					'created' => $row['created']
				];
			}
		}
		$this->success(__('OK'),$list);
	}

	/**
	 * 发送消息
	 *
	 * @ApiSummary  (WanlChat 发送即使消息)
	 * @ApiMethod   (POST)
	 *
	 * @param string $message 消息内容JSON
	 */
	public function send()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			// 判断服务是否启动
			if(!$this->wanlchat->isWsStart()){
				$this->error('请启动IM即时通讯服务');
			}
			$message = $this->request->post();
			$message['type'] = 'chat'; //用户唯一发送口，加chat防止伪装客服或其他类型消息
			$message?'':($this->error(__('Invalid parameters')));
			if($message['form']['id'] != $this->auth->id){
				$this->error(__('非法访问'));
			}
			// 判断是否为自己
			if($message['form']['id'] == $message['to_id']){
				$this->error(__('不允许自己和自己聊天'));
			}
			// 查询是否在线
			$online = $this->wanlchat->isOnline($message['to_id']);
			// 保存聊天记录到服务器
			$data = model('app\api\model\flbooth\Chat');
			$data->form_uid = $message['form']['id'];
			$data->to_id = $message['to_id'];
			$data->form = json_encode($message['form']);
			$data->message = json_encode($message['message']);
			$data->type = $message['type'];
			$data->online = $online;
			$data->save();
			$message['id'] = $data->id;
			// 在线发送
			$online == 1 ? ($this->wanlchat->send($message['to_id'], $message)) : '';
			$this->success(__('发送成功'), []);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 查询IM服务器状态
	 *
	 * @ApiSummary  (WanlChat 查询用户历史消息)
	 * @ApiMethod   (GET)
	 */
	public function state()
	{
		if(!$this->wanlchat->isWsStart()){
			$this->error('通讯服务未正常启用直播、登录服务暂停');
		}else{
			$this->success(__('IM服务器已启动'));
		}
	}
	
	/**
	 * 查询用户聊天记录
	 *
	 * @ApiSummary  (WanlChat 查询用户历史消息)
	 * @ApiMethod   (POST)
	 *
	 * @param string $to_id 接受ID
	 */
	public function history()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$id = $this->request->post('to_id');
			$id?'':($this->error(__('Invalid parameters')));
			$uid = $this->auth->id;
			// 查询历史记录
			$result = model('app\api\model\flbooth\Chat')
				->where("((form_uid={$uid} and to_id={$id}) or (form_uid={$id} and to_id={$uid})) and type='chat'")
				->whereTime('created', 'month')
				->order('created Desc')
				->paginate();
			$this->success(__('发送成功'), $result);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 全部已读
	 *
	 * @ApiSummary  (WanlChat 已读店铺消息)
	 * @ApiMethod   (POST)
	 */
	public function read()
	{
		if($this->request->isPost()){
			$uid = $this->auth->id;
			$data = model('app\api\model\flbooth\Chat')
				->where(['to_id' => $uid, 'isread' => 0, 'type' => 'chat'])
				->update(['isread' => 1]);	
			$this->success(__('更新成功'), []);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 已读店铺消息
	 *
	 * @ApiSummary  (WanlChat 已读店铺消息)
	 * @ApiMethod   (POST)
	 *
	 * @param string $shop_id 店铺ID
	 */
	public function clear()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$id = $this->request->post('id');
			$id?'':($this->error(__('Invalid parameters')));
			$uid = $this->auth->id;
			// 设置成已读
			$data = model('app\api\model\flbooth\Chat')
				->where(['form_uid' => $id, 'to_id' => $uid, 'isread' => 0])
				->update(['isread' => 1]);	
			$this->success(__('更新成功'), $data);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 删除指定聊天记录
	 *
	 * @ApiSummary  (WanlChat 删除指定聊天记录)
	 * @ApiMethod   (POST)
	 *
	 * @param string $shop_id 店铺ID
	 */
	public function del()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$id = $this->request->post('id');
			$id ? '' : ($this->error(__('Invalid parameters')));
			$uid = $this->auth->id;
			// 设置成已读
			$data = model('app\api\model\flbooth\Chat')
				->where("((form_uid={$uid} and to_id={$id}) or (form_uid={$id} and to_id={$uid})) and type='chat'")
				->delete();
			$this->success(__('更新成功'), $data);
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 加载欢迎消息
	 *
	 * @ApiSummary  (WanlChat 加载欢迎消息)
	 * @ApiMethod   (POST)
	 */
	public function hello()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$post = $this->request->post();
			if($post['type'] == 'service'){
				$data['id'] = $post['id'] + 1;
				$data['type'] = 'service';
				$data['form']['id'] = 0;
				$data['message']['type'] = 'text'; //默认消息
				$data['message']['content']['text'] = $this->chatConfig['config']['auth_reply'];
				$data['created'] = time();
				$this->wanlchat->send($post['form_id'], $data);
			}else if($post['type'] == 'shop'){
				
			}
			$this->success(__('请求成功'));
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 智能客服
	 *
	 * @ApiSummary  (WanlChat 智能小秘)
	 * @ApiMethod   (POST)
	 */
	public function service()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if($this->request->isPost()){
			$post = $this->request->post();
			$form_id = $post['form']['id'];
			if($post['message']['type'] == 'text'){
				$content = $post['message']['content']['text'];
			}
			$data['id'] = $post['id'] + 1;
			$data['type'] = 'service';
			$data['form']['id'] = 0;
			$data['message']['type'] = 'text'; //默认消息
			$data['created'] = time();
			if($post['to_id'] == 0){
				if($post['message']['type'] == 'text'){
					if($content == '人工客服' || $content == '客服' || $content == '人工'){
						// 查询 哪个后台在线
						$online = [];
						$admin = model('app\api\model\flbooth\Admin')
							->field('id,nickname,avatar')
							->select();
						foreach($admin as $user){
							if($this->wanlchat->isOnline(bcadd(8080000,$user['id'])) == 1){
								 $online[] = $user;
							}
						}
						if(count($online) == 0){
							$data['message']['content']['text'] = $this->chatConfig['config']['not_online'];
						}else{
							$key = mt_rand(0,count($online)-1);
							$data['form']['id'] = bcadd(8080000, $online[$key]['id']); // 随机发送一个在线管理员
							$data['form']['name'] = $online[$key]['nickname'];
							$data['form']['avatar'] = $online[$key]['avatar'];
							$data['message']['content']['text'] = $this->chatConfig['config']['service_initial'];
						}
						$this->wanlchat->send($form_id, $data);
					}else{
						$list = model('app\api\model\flbooth\Article')
							->where('keywords',$content)
							->field('id,title,content')
							->find();
						if($list){
							$data['message']['type'] = 'article';
							$data['message']['content'] = $list;
						}else{
							$arr = explode(' ',$content);
							$like = [];
							foreach($arr as $value){
								$like[] = '%'.$value.'%';
							}
							$article = model('app\api\model\flbooth\Article')
								->where('title|content','like',$like,'OR')
								->field('id,title,keywords')
								->select();
							$data['message']['type'] = 'list';
							$data['message']['content'] = $article;
						}
						$this->wanlchat->send($form_id, $data);
					}
				}else{
					if($post['message']['type'] == 'img'){
						$type = '图片消息';
					}
					if($post['message']['type'] == 'voice'){
						$type = '语音消息';
					}
					$data['message']['content']['text'] = '[可怜][委屈][委屈]，智能小秘暂无法识别“'.$type.'”，您可以与人工客服沟通时可以使用~~';
					$this->wanlchat->send($form_id, $data);
				}
			}else{
				$online = 1;
				// 保存聊天记录到服务器
				$data = model('app\api\model\flbooth\Chat');
				$data->form_uid = $post['form']['id'];
				$data->to_id = $post['to_id'];
				$data->form = json_encode($post['form']);
				$data->message = json_encode($post['message']);
				$data->type = $post['type'];
				$data->online = $online;
				$data->save();
				$post['id'] = $data->id;
				// 在线发送
				$this->wanlchat->send($post['to_id'], $post);
			}
			$this->success(__('请求成功'));
		}
		$this->error(__('非法请求'));
	}
}
