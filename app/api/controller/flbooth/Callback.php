<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use addons\flbooth\library\WanlChat\WanlChat;
use addons\flbooth\library\WanlPay\WanlPay;

use think\View;
use think\Log;

/**
 * flbooth 回调接口
 */
class Callback extends Api
{
    protected $noNeedLogin = ['*'];
	protected $noNeedRight = ['*'];
    
	
	public function _initialize()
	{
	    parent::_initialize();
	    
	}
	/**
	 * 接收快递100推送消息
	 *
	 * @ApiSummary  (flbooth 快递接口-接收快递100推送消息)
	 * @ApiMethod   (POST)
	 *
	 * @param string $status 物流状态 polling:监控中，shutdown:结束，abort:中止，updateall：重新推送
	 * @param array $lastResult 最新物流动态
	 */
	public function kuaidi()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $kuaidi = model('app\api\model\flbooth\KuaidiSub');
			$post = $this->request->post();
			// 接收消息
			try {
    			$param = json_decode($post["param"], true);
    			$status = $param['status']; // 状态 polling:监控中，shutdown:结束，abort:中止，updateall：重新推送
    			$message = $param['lastResult']['message']; // 消息体
    			$state = $param['lastResult']['state']; // 快递单当前状态，包括0在途，1揽收，2疑难，3签收，4退签，5派件，6退回，7转投
    			$ischeck = $param['lastResult']['ischeck']; // 是否签收标记
    			$nu = $param['lastResult']['nu']; // 快递单号
    			$com = $param['lastResult']['com']; // 快递公司编码
    			$data = $param['lastResult']['data']; // 数组，包含多个对象，每个对象字段如展开所示
    			// 查询快递是否存在
    		    $express = $kuaidi->get(['express_no' => $nu]);
    			if($express){
    			    // 判断来源
    			    if($post["sign"] != strtoupper(md5($post["param"].$express['sign']))){
    			        return json(["result" => false, "returnCode" => "405", "message" => "校验码错误"]);
    			    }
    			    // 更新数据
                    $express->message = $message;
                    $express->status = $status;
                    $express->state = $state;
                    $express->ischeck = $ischeck;
                    $express->com = $com;
                    $express->data = json_encode($data);
                    $express->save();
                    // 判断更新状态
        			if($express){
        			    return json(["result" => true, "returnCode" => "200", "message" => "接收成功"]);
        			} 
    			}else{
    			    return json(["result" => false, "returnCode" => "404", "message" => "快递单号不存在"]);
    			}
			} catch (Exception $e) {
                return json(["result" => false, "returnCode" => "500", "message" => "服务器错误"]);
            }
		}
		return json(["result" => false, "returnCode" => "500", "message" => "非正常访问"]);
	}
	
	/**
	 * 推流状态回调
	 *
	 * @ApiSummary  (flbooth 直播接口-推流状态回调)
	 * @ApiMethod   (POST)
	 *
	 * @param string $action 回调状态 publish / publish_done
	 * @param string $ip 回调地址ip
	 * @param string $id 推流流名
	 * @param string $app 推流域名
	 * @param string $appname 推流app名
	 * @param string $time timestamp
	 * @param string $usrargs 用户参数
	 * @param string $node 内部节点ip
	 */
	public function push($id, $action)
	{
		$row = model('app\api\model\flbooth\Live')->get(['liveid' => $id]);
		$find = model('app\api\model\flbooth\find\Find');
		if($row){
			if($action == 'publish'){
				$this->sendLiveGroup($id, ['type' => 'publish']);
				$row->save(['state' => 1]);
				// 避免多次推流，检查是否存在多个
				$count = $find->where('live_id', $row['id'])->count();
				// 发布动态
				if($count == 0){
					// 关联商品
					$goods = model('app\api\model\flbooth\Goods')
						->where('id', 'in', $row['goods_ids'])
						->limit(2)
						->select();
					$image = [$row['image']];
					foreach ($goods as $vo) {
						$image[] = $vo['image'];
					}
					// 1.1.2升级
					$shop = model('app\api\model\flbooth\Shop')->get($row['shop_id']);
					$user = model('app\api\model\flbooth\find\User')->get([
						'user_id' => $shop['user_id']
					]);
					// 保存数据
					$find->save([
						'shop_id' => $shop['id'],
						'user_id' => $shop['user_id'], // 1.1.2升级
						'user_no' => $user['user_no'], // 1.1.2升级
						'type' => 'live',
						'goods_ids' => $row['goods_ids'],
						'live_id' => $row['id'],
						'content' => $row['content'],
						'images' => implode(',', $image)
					]);
				}
			}else if($action == 'publish_done'){
				$this->sendLiveGroup($id, ['type' => 'publish_done']);
				$row->save(['state' => 2]);
			}
		}else{
			$this->error(__('没有找到相关推流'));
		}
		
	}
	
	/**
	 * 录制文件回调
	 *
	 * @ApiSummary  (flbooth 直播接口-录制文件回调)
	 * @ApiMethod   (POST)
	 *
	 * @param string $domain 回调状态 publish / publish_done
	 * @param string $app 回调地址ip
	 * @param string $stream 推流流名
	 * ------------------------------------
	 * @param string $event record_started/record_paused/record_resumed
	 *-------------------------------------
	 * @param string $uri 推流域名
	 * @param string $duration 推流app名
	 * @param string $start_time timestamp
	 * @param string $stop_time 用户参数
	 */
	public function record()
	{
		if ($this->request->isPost()) {
			$event = $this->request->post('event');
			$stream = $this->request->post('stream');
			$uri = $this->request->post('uri');
			
		    if($event == 'record_started'){
			    // 录制开始
    		}else if($event == 'record_paused'){
    			// 录制暂停
    		}else if($event == 'record_resumed'){
    			// 录制继续
    		}else{
    			// 录制成功
    			if($uri && $stream){
    				$config = get_addon_config('flbooth');
    				$live = model('app\api\model\flbooth\Live')
						->where('liveid', $stream)
						->find();
					// 修改CND拼接域名 1.1.2升级
					$live->save(['recordurl' => ($config['live']['sslSwitch'] == 'Y' ? 'https://' : 'http://') . $config['live']['liveCnd'] . '/' . $uri]);
					// 修改发现状态 1.1.4升级
					$find = model('app\api\model\flbooth\find\Find')
						->where('live_id', $live['id'])
						->find();
					if($find){
						$find->save(['state' => 'normal']);
					}
    			}else{
    				$this->error(__('录制失败'));
    			}
    		}
		}
		$this->error(__('非正常访问'));
	}
	//file:///C:/Users/flbooth/Desktop/flbooth-1.1.3/application/api/controller/flbooth/Callback.php
	
	/**
	 * 安全审核
	 *
	 * @ApiSummary  (flbooth 直播接口-安全审核)
	 * @ApiMethod   (POST)
	 *
	 * @param string $DomainName 用户域名
	 * @param string $AppName  App名
	 * @param string $StreamName 流名
	 * @param string $OssEndpoint 存储对象 Endpoint
	 * @param string $OssBucket 存储对象的 Bucket
	 * @param string $OssObject 存储对象的文件名
	 * @param array $Result 参数
	 */
	public function detectporn($StreamName, $Result)
	{
		$res = $Result[0]['Result'][0];
		if($res['Suggestion'] == 'block'){ // 违规
			$live = model('app\api\model\flbooth\Live')->get(['liveid' => $StreamName]);
			model('app\api\model\flbooth\find\Find')->where(['live_id' => $live['id']])->delete();
			$live->save(['gestion' => $res['Scene'], 'state' => 3]);
			// 封禁直播间
			$this->sendLiveGroup($StreamName, ['type' => 'ban']);
			
		}else if($res['Suggestion'] == 'review'){ // 直播间存在违规
			$this->sendLiveGroup($StreamName, [
				'type' => 'review',
				'text' => '直播间存在违规，请主播及时更正'
			]);
		}
	}
	
	/**
	 * 视频点播回调
	 *
	 * @ApiSummary  (flbooth 视频接口-视频点播回调)
	 * @ApiMethod   (POST)
	 *
	 * https://help.aliyun.com/document_detail/55627.html
	 */
	public function vod()
	{
		if ($this->request->isPost()) {
			$server = $this->request->server();
			$params = $this->request->post();
			$config = get_addon_config('flbooth');
			if($server['HTTP_X_VOD_SIGNATURE'] == md5($this->request->url(true).'|'.$server['HTTP_X_VOD_TIMESTAMP'].'|'.$config['video']['privateKey']) && $params['Status'] == 'success'){
				$data = [];
				$videoId = $params['EventType'] == 'AIMediaAuditComplete' ? $params['MediaId'] : $params['VideoId'];
				// 创建视频
				if($params['EventType'] == 'FileUploadComplete'){
					$video = model('app\api\model\flbooth\Video');
					$video->video_id = $videoId;
					$video->state = 'screenshot';
					$video->save();
					// 发现更新条件
					$data['state'] = 'screenshot';
				}else{
					$videoData = model('app\api\model\flbooth\Video')
						->where('video_id', $videoId)
						->find();
					// 视频截图完成
					if($params['EventType'] == 'SnapshotComplete'){
						$videoData->save([
							'cover_url' => $params['CoverUrl'], // 封面图片地址
							'snapshots' => $params['Snapshots'], // 截图列表---
							'state' => 'transcoding'
						]);
						// 发现更新条件
						$data['images'] = implode(",", $params['Snapshots']);
						$data['state'] = 'transcoding';
					}
					// 智能审核完成
					if($params['EventType'] == 'AIMediaAuditComplete'){
					    $state = 'pass';
						// 发现更新条件
						$complete = json_decode($params['Data'], true);
						if($complete['Suggestion'] == 'block'){
						    $state = 'hazard';
							$data['state'] = $state;
						}else{
							$data['state'] = $state;
						}
						// 更新视频
						$videoData->save([
						    'suggestion' => $complete['Suggestion'],
						    'state' => $state
						]);
					}
					// 视频转码完成
					if($params['EventType'] == 'TranscodeComplete'){
						$stream = $params['StreamInfos'][0];
						// 更新视频
						$videoData->save([
							'bitrate' => $stream['Bitrate'], // 视频流码率，单位：Kbps
							'definition' => $stream['Definition'], // 视频流清晰度定义
							'duration' => $stream['Duration'], // 视频流长度，单位：秒
							'url' => $stream['FileUrl'], // 视频流的播放地址
							'format' => $stream['Format'], // 视频流格式
							'fps' => $stream['Fps'], // 视频流帧率，每秒多少帧
							'height' => $stream['Height'], // 视频流高度，单位：px
							'width' => $stream['Width'], // 视频流宽度，单位：px
							'size' => $stream['Size'], // 视频流大小，单位：Byte
							'fit' => $stream['Height'] > $stream['Width'] ? 'cover' : 'contain', // 视频的表现形式
							'state' => 'normal'
						]);
						// 发现更新条件
						$data['state'] = 'normal';
					}
				}
				// 查询FIND是否已存在
				$find = model('app\api\model\flbooth\find\Find')
					->where('video_id', $videoId)
					->find();
				// 更新状态
				if($find){
					if($find['state'] == 'examine'){
						$data['state'] = 'examine';
					}
					$find->save($data);
				}
				$this->success();
			}
		}
		$this->error(__('非正常访问'));
	}
	
	/**
	 * 支付成功回调
	 *
	 * @ApiSummary  (flbooth 支付接口-支付成功回调)
	 * @ApiMethod   (POST)
	 *
	 */
	public function notify($type)
    {
        if(empty($type)){
            $this->error(__('非正常访问'));
        }
        $wanlpay = new WanlPay($type);
        $result = $wanlpay->notify();
        if($result['code'] == 200){
            return $result['msg'];
        }else{
            Log::write($result, 'debug');
        }
    }
    
    /**
	 * 支付成功回调
	 *
	 * @ApiSummary  (flbooth 支付接口-支付成功回调)
	 * @ApiMethod   (POST)
	 *
	 */
	public function notify_recharge($type)
    {
        if(empty($type)){
            $this->error(__('非正常访问'));
        }
        $wanlpay = new WanlPay($type);
        $result = $wanlpay->notify_recharge();
        if($result['code'] == 200){
            return $result['msg'];
        }else{
            Log::write($result, 'debug');
        }
    }
	
	/**
	 * 支付成功返回
	 *
	 * @ApiSummary  (flbooth 支付接口-支付成功返回)
	 * @ApiMethod   (POST)
	 *
	 */
	public function return($type)
	{
		if(empty($type)){
            $this->error(__('非正常访问'));
        }
        $view = new View();
        $wanlpay = new WanlPay($type);
        $config = get_addon_config('flbooth');
        $view->row = $wanlpay->return();
        $view->config = $config['h5'];
        return $view->fetch('index@flbooth/page/success');
	}
	
	/**
	 * 发送直播群组消息
	 * 内部方法
	 */
	private function sendLiveGroup($group, $message)
	{
		$wanlchat = new WanlChat();
		$wanlchat->sendGroup($group, [
			'type' => 'live',
			'group' => $group,
			'form' => [
				'id' => 0,
				'nickname' => '系统'
			],
			'message' => $message,
			'online' => 0,
			'like' => 0
		]);
	}
}
	
	
	
	