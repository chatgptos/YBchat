<?php

namespace app\api\controller\flbooth;

use addons\flbooth\library\Decrypt\weixin\wxBizDataCrypt;
use addons\flbooth\library\WanlChat\WanlChat;
use addons\flbooth\library\WeixinSdk\Security;
use app\common\controller\Api;
use app\common\library\Ems;
use app\common\library\Sms;

use fast\Random;
use fast\Http;
use think\Validate;

/**
 * flbooth会员接口
 */
class User extends Api
{
    protected $noNeedLogin = ['login', 'logout', 'mobilelogin', 'register', 'resetpwd', 'changeemail', 'changemobile', 'third', 'phone', 'perfect', 'demo'];
    protected $noNeedRight = ['*'];
    
    public function _initialize()
    {
        parent::_initialize();
        //WanlChat 即时通讯调用
		$this->wanlchat = new WanlChat();
		$this->auth->setAllowFields(['id','username','nickname','mobile','avatar','level','gender','birthday','bio','money','score','successions','maxsuccessions','prev_time','login_time','loginip','join_time']);
    }

	// 调试接口
	public function demo()
	{
		
	}


    /**
     * 会员登录
     * @ApiMethod   (POST)
     * @param string $account  账号
     * @param string $password 密码
     */
    public function login()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$account = $this->request->post('account');
			$password = $this->request->post('password');
			$client_id = $this->request->post('client_id');
			if (!$account || !$password) {
				$this->error(__('Invalid parameters'));
			}
			$ret = $this->auth->login($account, $password);
			if ($ret) {
			    if($client_id){
			        $this->wanlchat->bind($client_id, $this->auth->id);
			    }
				$this->success(__('Logged in successful'), self::userInfo());
			} else {
				$this->error($this->auth->getError());
			}
		}
		$this->error(__('非法请求'));
    }

    /**
     * 手机验证码登录
     * @ApiMethod   (POST)
     * @param string $mobile  手机号
     * @param string $captcha 验证码
     */
    public function mobilelogin()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$mobile = $this->request->post('mobile');
			$captcha = $this->request->post('captcha');
			$client_id = $this->request->post('client_id');
			if (!$mobile || !$captcha) {
				$this->error(__('Invalid parameters'));
			}
			if (!Validate::regex($mobile, "^1\d{10}$")) {
				$this->error(__('Mobile is incorrect'));
			}
			if (!Sms::check($mobile, $captcha, 'mobilelogin')) {
				$this->error(__('Captcha is incorrect'));
			}
			$user = \app\common\model\User::getByMobile($mobile);
			if ($user) {
				if ($user->status != 'normal') {
					$this->error(__('Account is locked'));
				}
				//如果已经有账号则直接登录
				$ret = $this->auth->direct($user->id);
			} else {
				$ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
			}
			if ($ret) {
				Sms::flush($mobile, 'mobilelogin');
				if($client_id){
			        $this->wanlchat->bind($client_id, $this->auth->id);
			    }
				$this->success(__('Logged in successful'), self::userInfo());
			} else {
				$this->error($this->auth->getError());
			}
		}
		$this->error(__('非法请求'));
    }
    
    /**
     * 手机号登录
     * @ApiMethod   (POST)
     * @param string $encryptedData  
     * @param string $iv  
     */
    public function phone()
    {
        //设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$post = $this->request->post();
		    if (!isset($post['iv'])) {
		        $this->error(__('获取手机号异常'));
		    }
		    // 获取配置
		    $config = get_addon_config('flbooth');
		    // 微信小程序一键登录
	        $params = [
			    'appid'    => $config['mp_weixin']['appid'],
			    'secret'   => $config['mp_weixin']['appsecret'],
			    'js_code'  => $post['code'],
			    'grant_type' => 'authorization_code'
			    ];
		    $result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
		    $json = (array)json_decode($result['msg'], true);
		    // 判断third是否存在ID,存在快速登录
			if(isset($json['unionid'])){
				$third = model('app\api\model\flbooth\Third')->get(['platform' => 'mp_weixin', 'unionid' => $json['unionid']]);
			}else{
				$third = model('app\api\model\flbooth\Third')->get(['platform' => 'mp_weixin', 'openid' => $json['openid']]);
			}
		    if ($third && $third['user_id'] != 0) {
		        //如果已经有账号则直接登录
    			$ret = $this->auth->direct($third['user_id']);
		    } else {
    		    // 手机号解码
    		    $decrypt = new wxBizDataCrypt($config['mp_weixin']['appid'], $json['session_key']);
                $decrypt->decryptData($post['encryptedData'], $post['iv'], $data);
                $data = (array)json_decode($data, true);
                // 开始登录
    		    $mobile = $data['phoneNumber'];
    			$user = \app\common\model\User::getByMobile($mobile);
    			if ($user) {
    				if ($user->status != 'normal') {
    					$this->error(__('Account is locked'));
    				}
    				//如果已经有账号则直接登录
    				$ret = $this->auth->direct($user->id);
    			} else {
    				$ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
    			}
		    }
			
		    if ($ret) {
		        if (isset($post['client_id']) && $post['client_id'] != null) {
    		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
    		    }
    			$this->success(__('Logged in successful'), self::userInfo());
    		} else {
    			$this->error($this->auth->getError());
    		}
		}
		$this->error(__('非法请求'));
    }
    
    
    /**
     * 注册会员
     * @ApiMethod   (POST)
     * @param string $mobile   手机号
     * @param string $code   验证码
     */
    public function register()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$mobile = $this->request->post('mobile');
			$code = $this->request->post('captcha');
			$client_id = $this->request->post('client_id');
			if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
				$this->error(__('Mobile is incorrect'));
			}
			$ret = Sms::check($mobile, $code, 'register');
			if (!$ret) {
				$this->error(__('Captcha is incorrect'));
			}
			$ret = $this->auth->register($mobile, Random::alnum(), '', $mobile, []);
			if ($ret) {
			    if($client_id){
			        $this->wanlchat->bind($client_id, $this->auth->id);
			    }
				$this->success(__('Sign up successful'), self::userInfo());
			} else {
				$this->error($this->auth->getError());
			}
		}
		$this->error(__('非法请求'));
    }

    /**
     * 注销登录
     */
    public function logout($client_id = null)
    {
        // 踢出即时通讯 1.0.7升级
        if($client_id){
            $this->wanlchat->destoryClient($client_id);
        }
        // 退出登录
        $this->auth->logout();
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     * @ApiMethod   (POST)
	 *
     * @param string $avatar   头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $bio      个人简介
     */
    public function profile()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$user = $this->auth->getUser();
			$avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
			if($avatar){
				$user->avatar = $avatar;
			}else{
				$username = $this->request->post('username');
				$nickname = $this->request->post('nickname');
				$bio = $this->request->post('bio');
				
				$config = get_addon_config('flbooth');
				$security = new Security($config['mp_weixin']['appid'], $config['mp_weixin']['appsecret']);
				if($bio){
    				$bioCheck = $security->check('msg_sec_check', ['content' => $bio]);
    				if($bioCheck['code'] !== 0){
    					if($bioCheck['code'] === 87014){
    						$this->error(__('签名包含敏感词汇'));
    					}else{
    						$this->error(__($bioCheck['msg']));
    					}
    				}
				}
				if($nickname){
					$nicknameCheck = $security->check('msg_sec_check', ['content' => $nickname]);
    				if($nicknameCheck['code'] !== 0){
    					if($nicknameCheck['code'] === 87014){
    						$this->error(__('昵称包含敏感词汇'));
    					}else{
    						$this->error(__($nicknameCheck['msg']));
    					}
    				}
				}
				if ($username) {
				    $usernameCheck = $security->check('msg_sec_check', ['content' => $username]);
    				if($usernameCheck['code'] !== 0){
    					if($usernameCheck['code'] === 87014){
    						$this->error(__('用户名包含敏感词汇'));
    					}else{
    						$this->error(__($usernameCheck['msg']));
    					}
    				}
					$exists = \app\common\model\User::where('username', $username)->where('id', '<>', $this->auth->id)->find();
					if ($exists) {
						$this->error(__('Username already exists'));
					}
					$user->username = $username;
				}
				$user->nickname = $nickname;
				$user->bio = $bio;
			}
			$user->save();
			$this->success('返回成功',$user);
		}
		$this->error(__('非法请求'));
    }

    /**
     * 修改手机号
     * @ApiMethod   (POST)
     * @param string $email   手机号
     * @param string $captcha 验证码
     */
    public function changemobile()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$user = $this->auth->getUser();
			$mobile = $this->request->request('mobile');
			$captcha = $this->request->request('captcha');
			if (!$mobile || !$captcha) {
			    $this->error(__('Invalid parameters'));
			}
			if (!Validate::regex($mobile, "^1\d{10}$")) {
			    $this->error(__('Mobile is incorrect'));
			}
			if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
			    $this->error(__('Mobile already exists'));
			}
			$result = Sms::check($mobile, $captcha, 'changemobile');
			if (!$result) {
			    $this->error(__('Captcha is incorrect'));
			}
			$verification = $user->verification;
			$verification->mobile = 1;
			$user->verification = $verification;
			$user->mobile = $mobile;
			$user->save();
			
			Sms::flush($mobile, 'changemobile');
			$this->success();
		}
		$this->error(__('非法请求'));
    }
    
    /**
     * 重置密码
     * @ApiMethod   (POST)
     * @param string $mobile      手机号
     * @param string $newpassword 新密码
     * @param string $captcha     验证码
     */
    public function resetpwd()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$mobile = $this->request->post("mobile");
			$newpassword = $this->request->post("newpassword");
			$captcha = $this->request->post("captcha");
			if (!$newpassword || !$captcha || !$mobile) {
				$this->error(__('Invalid parameters'));
			}
			if (!Validate::regex($mobile, "^1\d{10}$")) {
				$this->error(__('Mobile is incorrect'));
			}
			$user = \app\common\model\User::getByMobile($mobile);
			if (!$user) {
				$this->error(__('User not found'));
			}
			$ret = Sms::check($mobile, $captcha, 'resetpwd');
			if (!$ret) {
				$this->error(__('Captcha is incorrect'));
			}
			Sms::flush($mobile, 'resetpwd');
			//模拟一次登录
			$this->auth->direct($user->id);
			$ret = $this->auth->changepwd($newpassword, '', true);
			if ($ret) {
				$this->success(__('Reset password successful'));
			} else {
				$this->error($this->auth->getError());
			}
		}
		$this->error(__('非法请求'));
    }
    
    /**
     * 第三方登录-web登录
     * @ApiMethod   (POST)
     * @param string $platform 平台名称
     */
    public function third_web()
    {
        $this->error(__('暂未开放'));
    }
    
    
    /**
     * 第三方登录
     * @ApiMethod   (POST)
     * @param string $platform 平台名称
     * @param string $code     Code码
     */
    public function third()
    {
        //设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    // 获取登录配置
			$config = get_addon_config('flbooth');
			// 获取前端参数
			$post = $this->request->post();
			// 登录项目
			$time = time();
			$platform = $post['platform'];

			// 开始登录
			switch ($platform)
			{
				// 微信小程序登录
				case 'mp_weixin':
					$params = [
						'appid'      => $config[$platform]['appid'],
						'secret'     => $config[$platform]['appsecret'],
						'js_code'    => $post['loginData']['code'],
						'grant_type' => 'authorization_code'
					];
					$result = Http::sendRequest("https://api.weixin.qq.com/sns/jscode2session", $params, 'GET');
					if ($result['ret']) {
					    $json = (array)json_decode($result['msg'], true);
						if(isset($json['unionid'])){
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_open', 'unionid' => $json['unionid']]);
						}else{
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_open', 'openid' => $json['openid']]);
						}
                        // 成功登录
                        if ($third) {
                            $user = model('app\common\model\User')->get($third['user_id']);
                            if (!$user) {
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third['token']
                                ]);
                            }
                            $third->save([
                                'access_token' => $json['session_key'],
                                'expires_in' => 7776000,
                                'login_time' => $time,
                                'expiretime' => $time + 7776000
                            ]);
                            $ret = $this->auth->direct($user->id);
                            if ($ret) {
                			    if (isset($post['client_id']) && $post['client_id'] != null) {
                    		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                    		    }
                				$this->success(__('Sign up successful'), self::userInfo());
                			} else {
                				$this->error($this->auth->getError());
                			}
                        } else {
                            // 新增$third
                            $third = model('app\api\model\flbooth\Third');
                            $third->platform  = 'weixin_open';
							if(isset($json['unionid'])){
								$third->unionid  = $json['unionid'];
							}else{
								$third->openid  = $json['openid'];
							}
                            $third->access_token  = $json['session_key'];
                            $third->expires_in  = 7776000;
                            $third->login_time  = $time;
                            $third->expiretime  = $time + 7776000;
                            // 判断当前是否登录
                            if($this->auth->isLogin()){
								if (isset($post['client_id']) && $post['client_id'] != null) {
								    $this->wanlchat->bind($post['client_id'], $this->auth->id);
								}
                                $third->user_id  = $this->auth->id;
                                $third->save();
                                // 直接绑定自动完成
                                $this->success('绑定成功', [
                                    'binding' => 1
                                ]);
                            } else {
								$third->token  = Random::uuid();
                                $third->save();
                                // 通知客户端绑定
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third->token
                                ]);
                            }
                        }
					}else{
						$this->error('API异常，微信小程序登录失败'); 
					}
					break;
					
				// 微信App登录
				case 'app_weixin':
					$params = [
						'access_token' => $post['loginData']['authResult']['access_token'],
						'openid' => $post['loginData']['authResult']['openid']
					];
					$result = Http::sendRequest("https://api.weixin.qq.com/sns/userinfo", $params, 'GET');
					if ($result['ret']) {
					    $json = (array)json_decode($result['msg'], true);
						if(isset($json['unionid'])){
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_open', 'unionid' => $json['unionid']]);
						}else{
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_open', 'openid' => $json['openid']]);
						}
					    // 成功登录
                        if ($third) {
                            $third->save([
                                'access_token' => $post['loginData']['authResult']['access_token'],
                                'refresh_token' => $post['loginData']['authResult']['refresh_token'],
                                'expires_in' => $post['loginData']['authResult']['expires_in'],
                                'login_time' => $time,
                                'expiretime' => $time + $post['loginData']['authResult']['expires_in']
                            ]);
                            $ret = $this->auth->direct($third['user_id']);
                            if ($ret) {
                                if (isset($post['client_id']) && $post['client_id'] != null) {
                    		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                    		    }
                				$this->success(__('Sign up successful'), self::userInfo());
                			} else {
                				$this->error($this->auth->getError());
                			}
                        } else {
                            // 新增$third
                            $third = model('app\api\model\flbooth\Third');
                            $third->platform  = 'weixin_open';
							if(isset($json['unionid'])){
								$third->unionid  = $json['unionid'];
							}else{
								$third->openid  = $json['openid'];
							}
                            $third->access_token  = $post['loginData']['authResult']['access_token'];
                            $third->refresh_token  = $post['loginData']['authResult']['refresh_token'];
                            $third->expires_in  = $post['loginData']['authResult']['expires_in'];
                            $third->login_time  = $time;
                            $third->expiretime  = $time + $post['loginData']['authResult']['expires_in'];
                            // 判断当前是否登录,否则注册
                            if($this->auth->isLogin()){
								if (isset($post['client_id']) && $post['client_id'] != null) {
								    $this->wanlchat->bind($post['client_id'], $this->auth->id);
								}
                                $third->user_id  = $this->auth->id;
                                $third->save();
                                // 直接绑定自动完成
                                $this->success('绑定成功', [
                                    'binding' => 1
                                ]);
                            } else {
                                $username = $json['nickname'];
								$auth = [];
                                $mobile = '';
                                $gender = $json['sex'] == 1 ? 1 : 0;
                                $avatar = $json['headimgurl'];
								// 1.1.3升级
								if(isset($json['unionid'])){
									// 1.1.3升级 查询其他unionid的user_id进行登录
									$unionid = model('app\api\model\flbooth\Third')
									    ->where('user_id','<>', 0)
									    ->where('unionid','=', $json['unionid'])
									    ->find();
									if($unionid){
										$auth = $this->auth->direct($unionid['user_id']);
									}else{
										// 注册账户
										$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
										    'gender' => $gender, 
										    'nickname' => $username, 
										    'avatar' => $avatar
										]);
									}
								}else{
									// 注册账户
									$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
									    'gender' => $gender, 
									    'nickname' => $username, 
									    'avatar' => $avatar
									]);
								}
                    			if ($auth) {
                    			    if (isset($post['client_id']) && $post['client_id'] != null) {
                        		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                        		    }
                    				// 更新第三方登录
                    			    $third->user_id  = $this->auth->id;
                    			    $third->openname  = $username;
                    			    $third->save();
                    				$this->success(__('Sign up successful'), self::userInfo());
                    			} else {
                    				$this->error($this->auth->getError());
                    			}
                            }
                        }
					}else{
					    $this->error('API异常，App登录失败'); 
					}
					break;
				// 微信公众号登录
				case 'h5_weixin':
					$params = [
					    'appid'      => $config['sdk_qq']['gz_appid'],
					    'secret'     => $config['sdk_qq']['gz_secret'],
					    'code'       => $post['code'],
					    'grant_type' => 'authorization_code'
					];
					$result = Http::sendRequest('https://api.weixin.qq.com/sns/oauth2/access_token', $params, 'GET');
					if ($result['ret']) {
						$access = (array)json_decode($result['msg'], true);
						//获取用户信息
						$queryarr = [
							"access_token" => $access['access_token'],
							"openid"       => $access['openid']
						];
						$ret = Http::sendRequest("https://api.weixin.qq.com/sns/userinfo", $queryarr, 'GET');
						if ($ret['ret']) {
							$json = (array)json_decode($ret['msg'], true);
							if(isset($json['unionid'])){
								$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_h5', 'unionid' => $json['unionid']]);
							}else{
								$third = model('app\api\model\flbooth\Third')->get(['platform' => 'weixin_h5', 'openid' => $json['openid']]);
							}
							// 成功登录
							if ($third) {
							    $third->save([
									'openid' => $json['openid'], // 1.1.2升级
							        'access_token' => $access['access_token'],
							        'refresh_token' => $access['refresh_token'],
							        'expires_in' => $access['expires_in'],
							        'login_time' => $time,
							        'expiretime' => $time + $access['expires_in']
							    ]);
								// 登录客户端
							    $ret = $this->auth->direct($third['user_id']);
							    if ($ret) {
							        if (isset($post['client_id']) && $post['client_id'] != null) {
								        $this->wanlchat->bind($post['client_id'], $this->auth->id);
								    }
									$this->success(__('Sign up successful'), self::userInfo());
								} else {
									$this->error($this->auth->getError());
								}
							} else {
							    // 新增$third
							    $third = model('app\api\model\flbooth\Third');
							    $third->platform  = 'weixin_h5';
								// 1.1.2升级
								if(isset($json['unionid'])){
									$third->unionid  = $json['unionid'];
									$third->openid  = $json['openid'];
								}else{
									$third->openid  = $json['openid'];
								}
							    $third->access_token  = $access['access_token'];
							    $third->refresh_token  = $access['refresh_token'];
							    $third->expires_in  = $access['expires_in'];
							    $third->login_time  = $time;
							    $third->expiretime  = $time + $access['expires_in'];
							    // 获取到的用户信息
							    $username = $json['nickname'];
								$auth = [];
							    $mobile = '';
							    $gender = $json['sex'] == 1 ? 1 : 0;
							    $avatar = $json['headimgurl'];
								
								// 1.1.3升级
								if(isset($json['unionid'])){
									// 1.1.3升级 查询其他unionid的user_id进行登录
									$unionid = model('app\api\model\flbooth\Third')
									    ->where('user_id','<>', 0)
									    ->where('unionid','=', $json['unionid'])
									    ->find();
										
									if($unionid){
										$auth = $this->auth->direct($unionid['user_id']);
									}else{
										// 注册账户
										$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
										    'gender' => $gender, 
										    'nickname' => $username, 
										    'avatar' => $avatar
										]);
									}
								}else{
									// 注册账户
									$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
									    'gender' => $gender, 
									    'nickname' => $username, 
									    'avatar' => $avatar
									]);
								}
								
							    if ($auth) {
							        if (isset($post['client_id']) && $post['client_id'] != null) {
							            $this->wanlchat->bind($post['client_id'], $this->auth->id);
							        }
							    	// 更新第三方登录
							        $third->user_id  = $this->auth->id;
							        $third->openname  = $username;
							        $third->save();
							    	$this->success(__('Sign up successful'), self::userInfo());
							    } else {
							    	$this->error($this->auth->getError());
							    }
							}
						}else{
							$this->error('获取用户信息失败！'); 
						}
					}else{
						$this->error('获取openid失败！'); 
					}
					break;
				// QQ小程序登录
				case 'mp_qq':
					$params = [
						'appid'      => $config[$platform]['appid'],
						'secret'     => $config[$platform]['appsecret'],
						'js_code'    => $post['loginData']['code'],
						'grant_type' => 'authorization_code'
					];
					$result = Http::sendRequest("https://api.q.qq.com/sns/jscode2session", $params, 'GET');
					if ($result['ret']) {
					    $json = (array)json_decode($result['msg'], true);
						if(isset($json['unionid'])){
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'qq_open', 'unionid' => $json['unionid']]);
						}else{
							$third = model('app\api\model\flbooth\Third')->get(['platform' => 'qq_open', 'openid' => $json['openid']]);
						}
                        // 成功登录
                        if ($third) {
                            $user = model('app\common\model\User')->get($third['user_id']);
                            if (!$user) {
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third['token']
                                ]);
                            }
                            $third->save([
                                'access_token' => $json['session_key'],
                                'expires_in' => 7776000,
                                'login_time' => $time,
                                'expiretime' => $time + 7776000
                            ]);
                            $ret = $this->auth->direct($user->id);
                            if ($ret) {
                                if (isset($post['client_id']) && $post['client_id'] != null) {
                    		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                    		    }
                				$this->success(__('Sign up successful'), self::userInfo());
                			} else {
                				$this->error($this->auth->getError());
                			}
                        } else {
                            // 新增$third
                            $third = model('app\api\model\flbooth\Third');
                            $third->platform  = 'qq_open';
							if(isset($json['unionid'])){
								$third->unionid  = $json['unionid'];
							}else{
								$third->openid  = $json['openid'];
							}
                            $third->access_token  = $json['session_key'];
                            $third->expires_in  = 7776000;
                            $third->login_time  = $time;
                            $third->expiretime  = $time + 7776000;
                            // 判断当前是否登录
                            if($this->auth->isLogin()){
								// 1.1.4升级
								if (isset($post['client_id']) && $post['client_id'] != null) {
								    $this->wanlchat->bind($post['client_id'], $this->auth->id);
								}
                                $third->user_id  = $this->auth->id;
                                $third->save();
                                // 直接绑定自动完成
                                $this->success('绑定成功', [
                                    'binding' => 1
                                ]);
                            } else {
								$third->token  = Random::uuid();
                                $third->save();
                                // 通知客户端绑定
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third->token
                                ]);
                            }
                        }
					}else{
						$this->error('API异常，微信小程序登录失败'); 
					}
					break; 
					
				// QQ App登录
				case 'app_qq':
					$params = [
						'access_token' => $post['loginData']['authResult']['access_token']
					];
					$options = [
                        CURLOPT_HTTPHEADER  => [
                            'Content-Type: application/x-www-form-urlencoded'
                        ]
                    ];
					$result = Http::sendRequest("https://graph.qq.com/oauth2.0/me", $params, 'GET' ,$options);
					if ($result['ret']) {
					    $json = (array)json_decode(str_replace(" );","",str_replace("callback( ","",$result['msg'])), true);
					    if ($json['openid'] == $post['loginData']['authResult']['openid']) {
				            $third = model('app\api\model\flbooth\Third')->get(['platform' => 'qq_open', 'openid' => $json['openid']]);
    				        if ($third) {
    				            $user = model('app\common\model\User')->get($third['user_id']);
                                if (!$user) {
                                    $this->success('尚未绑定用户', [
                                        'binding' => 0,
                                        'token' => $third['token']
                                    ]);
                                }
    				            $third->save([
                                    'access_token' => $post['loginData']['authResult']['access_token'],
                                    'expires_in' => $post['loginData']['authResult']['expires_in'],
                                    'login_time' => $time,
                                    'expiretime' => $time + $post['loginData']['authResult']['expires_in']
                                ]);
                                $ret = $this->auth->direct($third['user_id']);
                                if ($ret) {
                                    if (isset($post['client_id']) && $post['client_id'] != null) {
                        		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                        		    }
                    				$this->success(__('Sign up successful'), self::userInfo());
                    			} else {
                    				$this->error($this->auth->getError());
                    			}
    				        } else {
    				            // 新增$third
                                $third = model('app\api\model\flbooth\Third');
                                $third->platform  = 'qq_open';
                                $third->openid  = $json['openid'];
                                $third->access_token  = $post['loginData']['authResult']['access_token'];
                                $third->expires_in  = $post['loginData']['authResult']['expires_in'];
                                $third->login_time  = $time;
                                $third->expiretime  = $time + $post['loginData']['authResult']['expires_in'];
                                // 判断当前是否登录
                                if($this->auth->isLogin()){
									if (isset($post['client_id']) && $post['client_id'] != null) {
									    $this->wanlchat->bind($post['client_id'], $this->auth->id);
									}
                                    $third->user_id  = $this->auth->id;
                                    $third->save();
                                    // 直接绑定自动完成
                                    $this->success('绑定成功', [
                                        'binding' => 1
                                    ]);
                                } else {
									$third->token  = Random::uuid();
                                    $third->save();
                                    // 通知客户端绑定
                                    $this->success('尚未绑定用户', [
                                        'binding' => 0,
                                        'token' => $third->token
                                    ]);
                                }
    				        }
					    } else {
					        $this->error(__('非法请求，机器信息已提交'));
					    }
					}else{
					    $this->error('API异常，App登录失败'); 
					}
					break;
				// QQ 网页登录
				case 'h5_qq':
					// 后续版本上线
					break; 
				// 微博App登录
				case 'app_weibo':
					$params = [
						'access_token' => $post['loginData']['authResult']['access_token']
					];
					$options = [
                        CURLOPT_HTTPHEADER  => [
                            'Content-Type: application/x-www-form-urlencoded'
                        ],
                        CURLOPT_POSTFIELDS => http_build_query($params),
                        CURLOPT_POST => 1
                    ];
					$result = Http::post("https://api.weibo.com/oauth2/get_token_info", $params, $options);
					$json = (array)json_decode($result, true);
				    if($json['uid'] == $post['loginData']['authResult']['uid']){
				        $third = model('app\api\model\flbooth\Third')->get(['platform' => 'weibo_open', 'openid' => $json['uid']]);
				        if ($third) {
				            $user = model('app\common\model\User')->get($third['user_id']);
                            if (!$user) {
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third['token']
                                ]);
                            }
				            $third->save([
                                'access_token' => $post['loginData']['authResult']['access_token'],
                                'expires_in' => $json['expire_in'],
                                'login_time' => $json['create_at'],
                                'expiretime' => $json['create_at'] + $json['expire_in']
                            ]);
                            $ret = $this->auth->direct($third['user_id']);
                            if ($ret) {
                                if (isset($post['client_id']) && $post['client_id'] != null) {
                    		        $this->wanlchat->bind($post['client_id'], $this->auth->id);
                    		    }
                				$this->success(__('Sign up successful'), self::userInfo());
                			} else {
                				$this->error($this->auth->getError());
                			}
				        } else {
				            // 新增$third
                            $third = model('app\api\model\flbooth\Third');
                            $third->platform  = 'weibo_open';
                            $third->openid  = $json['uid'];
                            $third->access_token  = $post['loginData']['authResult']['access_token'];
                            $third->expires_in  = $json['expire_in'];
                            $third->login_time  = $json['create_at'];
                            $third->expiretime  = $json['create_at'] + $json['expire_in'];
                            // 判断当前是否登录
                            if($this->auth->isLogin()){
								// 1.1.4升级
								if (isset($post['client_id']) && $post['client_id'] != null) {
								    $this->wanlchat->bind($post['client_id'], $this->auth->id);
								}
                                $third->user_id  = $this->auth->id;
                                $third->save();
                                // 直接绑定自动完成
                                $this->success('绑定成功', [
                                    'binding' => 1
                                ]);
                            } else {
								$third->token  = Random::uuid();
                                $third->save();
                                // 通知客户端绑定
                                $this->success('尚未绑定用户', [
                                    'binding' => 0,
                                    'token' => $third->token
                                ]);
                            }
				        }
				    }else{
				        $this->error(__('非法请求，机器信息已提交'));
				    }
					break; 
					
				// 小米App登录
				case 'app_xiaomi':
					
					break;
					
				// 苹果登录
				case 'apple':
					// 后续版本上线
					break; 
				default:
					$this->error('暂并不支持此方法登录');
			}
		}
		$this->error(__('10086非正常请求'));
    }

    /**
	 * 进一步完善资料
	 * @ApiMethod   (POST)
	 */
	public function perfect()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
		    $post = $this->request->post();
		    // 判断token没有绑定 1.1.4升级
		    $third = model('app\api\model\flbooth\Third')
				->where('token', '=', $post['token'])
				->find();
		        // 当user_id 不为空可以绑定
    		    if($third['user_id'] == 0 && $third){
    		        $username = $post['nickName'];
					$auth = [];
    		        $mobile = '';
    		        $gender = $post['gender'];
        		    $avatar = $post['avatarUrl'];
					// 1.1.4升级
					if($third['unionid']){
						// 1.1.3升级 查询其他unionid的user_id进行登录
						$unionid = model('app\api\model\flbooth\Third')
						    ->where('id','<>', $third['id'])
						    ->where('unionid','=', $third['unionid'])
						    ->find();
						if($unionid){
							$auth = $this->auth->direct($unionid['user_id']);
						}else{
							$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
							    'gender' => $gender, 
							    'nickname' => $username, 
							    'avatar' => $avatar
							]);
						}
					}else{
						$auth = $this->auth->register('u_'.Random::alnum(6), Random::alnum(), '', $mobile, [
						    'gender' => $gender, 
						    'nickname' => $username, 
						    'avatar' => $avatar
						]);
					}
        			if ($auth) {
						// 1.1.4升级
						if (isset($post['client_id']) && $post['client_id'] != null) {
						    $this->wanlchat->bind($post['client_id'], $this->auth->id);
						}
        				// 更新第三方登录
        				$third->save([
        			        'user_id' => $this->auth->id,
        			        'openname' => $username
        			    ]);
        				$this->success(__('Sign up successful'), self::userInfo());
        			} else {
        				$this->error($this->auth->getError());
        			}
    		    }else{
    		        $this->error(__('非法请求，机器信息已提交'));
    		    }
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 刷新用户中心
	 * @ApiMethod   (POST)
	 */
	public function refresh()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$this->success(__('刷新成功'), self::userInfo());
		}
		$this->error(__('非法请求'));
	}
	
	/**
	 * 数据统计 - 内部使用，开发者不要调用
	 */
	private function userInfo()
	{
		$user_id = $this->auth->id;
		// 查询订单
		$order = model('app\api\model\flbooth\Order')
			->where('user_id', $user_id)
			->select();
		$orderCount = array_count_values(array_column($order,'state'));
		
		// 物流列表
		$logistics = [];
		foreach ($order as $value)
		{
			if($value['state'] >=3 && $value['state'] <=6){
				//需要查询的订单
			}
		}
		// 统计数量
		$collection = [];
		$concern = [];
		// 1.1.0升级
		$footgoodsprint = [];
		$footgroupsprint = [];
		foreach (model('app\api\model\flbooth\GoodsFollow')->where('user_id', $user_id)->select() as $row) {
			if($row['goods_type'] === 'goods'){
				if(model('app\api\model\flbooth\Goods')->get($row['goods_id'])){
					$collection[] = $row['id'];
				}
			}else if($row['goods_type'] === 'groups'){
				if(model('app\api\model\flbooth\groups\Goods')->get($row['goods_id'])){
					$collection[] = $row['id'];
				}
			}
		}
		// 1.0.8升级  通过uuid查询足迹
		$uuid = $this->request->server('HTTP_UUID');
		if(!isset($uuid)){
			$charid = strtoupper(md5($this->request->header('user-agent').$this->request->ip()));
			$uuid = substr($charid, 0, 8).chr(45).substr($charid, 8, 4).chr(45).substr($charid,12, 4).chr(45).substr($charid,16, 4).chr(45).substr($charid,20,12);
		}
		foreach (model('app\api\model\flbooth\Record')->where('uuid', $uuid)->select() as $row) {
			if($row['goods_type'] === 'goods'){
				if(model('app\api\model\flbooth\Goods')->get($row['goods_id'])){
					$footgoodsprint[] = $row['goods_id'];
				}
			}else if($row['goods_type'] === 'groups'){
				if(model('app\api\model\flbooth\groups\Goods')->get($row['goods_id'])){
					$footgroupsprint[] = $row['goods_id'];
				}
			}
		}
		
		// 查询动态 、收藏夹、关注店铺、足迹、红包卡券
		$finish =  isset($orderCount[6]) ? $orderCount[6] : 0;
		$pay = isset($orderCount[1]) ? $orderCount[1] : 0;
		$delive = isset($orderCount[2]) ? $orderCount[2] : 0;
		$receiving = isset($orderCount[3]) ? $orderCount[3] : 0;
		$evaluate = isset($orderCount[4]) ? $orderCount[4] : 0;
		// 订单状态:1=待支付,2=待成团,3=待发货,4=待收货,5=待评论,6=已完成,7=已取消
		$groups = model('app\api\model\flbooth\groups\Order')
			->where('user_id', 'eq', $user_id)
			->where('state', 'neq', 7)
			->count();
	    return [
			'userinfo' => $this->auth->getUserinfo(),
			'statistics' => [
				'dynamic' => [
					'collection' => count($collection),
					'concern' => model('app\api\model\flbooth\find\Follow')->where('user_id', $user_id)->count(),
					'footprint' => count(array_flip($footgoodsprint)) + count(array_flip($footgroupsprint)),
					'coupon' => model('app\api\model\flbooth\CouponReceive')->where(['user_id' => $user_id, 'state' => '1'])->count(),
					'accountbank' => model('app\api\model\flbooth\PayAccount')->where('user_id', $user_id)->count()
				],
				'order' => [
					'whole' => $finish + $pay + $delive + $receiving + $evaluate,
					'groups' => $groups,
					'pay' => $pay,
					'delive' => $delive,
					'receiving' => $receiving,
					'evaluate' => $evaluate,
					'customer' => model('app\api\model\flbooth\Refund')->where(['state' => ['in','1,2,3,6'], 'user_id' => $this->auth->id])->count()
				],
				'logistics' => $logistics
			]
		];
	}
	
	/**
	 * 获取评论列表
	 *
	 * @ApiSummary  (flbooth 获取我的所有评论)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $list_rows  每页数量
	 * @param string $page  当前页
	 */
	public function comment()
	{
		$list = model('app\api\model\flbooth\GoodsComment')
			->where('user_id', $this->auth->id)
			->field('id,images,score,goods_id,order_goods_id,state,content,created')
			->order('created desc')
			->paginate()
			->each(function($data, $key){
				$data['order_goods'] = $data->order_goods ? $data->order_goods->visible(['id','title','image','price']):'';
				return $data;
			});
		$this->success('返回成功', $list);
	}
	
	/**
	 * 获取积分明细
	 */
	public function scoreLog()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$list = model('app\common\model\ScoreLog')
				->where('user_id', $this->auth->id)
				->order('created desc')
				->paginate();
			$this->success('ok',$list);
		}
		$this->error(__('非法请求'));
	}
	
}