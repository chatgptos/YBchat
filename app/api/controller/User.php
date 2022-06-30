<?php

namespace app\api\controller;

use ba\Captcha;
use app\common\facade\Token;
use app\common\controller\Frontend;
use think\exception\ValidateException;
use app\api\validate\User as UserValidate;




/**
 * 用户
 */
class User extends Frontend
{
    protected $noNeedLogin = ['checkIn', 'logout'];

    protected $noNeedPermission = ['index'];

    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $userInfo = $this->auth->getUserInfo();
        $menus    = $this->auth->getMenus();
        if (!$menus) {
            $this->error(__('No action available, please contact the administrator~'));
        }

        $userMenus = [];
        foreach ($menus as $menu) {
            if ($menu['type'] == 'menu_dir') {
                $userMenus[] = $menu;
            }
        }
        $this->success('', [
            'userInfo' => $userInfo,
            'menus'    => $userMenus,
        ]);
    }



    /**
     * 登录.
     *
     * @ApiTitle    (登录)
     * @ApiSummary  (登录)
     * @ApiMethod   (POST)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams   (name=tab, type=string, required=true, description="login")
     * @ApiParams   (name="username", type="integer", required=true, description="username")
     * @ApiParams   (name="password", type="integer", required=true, description="password")
     * @ApiParams   (name="captcha", type="integer", required=true, description="captcha")
     * @ApiParams   (name="captchaId", type="integer", required=true, description="captchaId")
     * @ApiParams   (name="keep", type="boole", required=true, description="keep")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
     * 'code':'1',
     * 'msg':'返回成功'
     * })
     */
    public function checkIn()
    {
        // 检查登录态
        if ($this->auth->isLogin()) {
            $this->success(__('You have already logged in. There is no need to log in again~'), [
                'routeName' => 'user'
            ], 302);
        }
        $params = $this->request->post(['tab', 'email', 'mobile', 'username', 'password', 'keep', 'captcha', 'captchaId']);
        if ($this->request->isPost()) {
            $params = $this->request->post(['tab', 'email', 'mobile', 'username', 'password', 'keep', 'captcha', 'captchaId']);
            if ($params['tab'] != 'login' && $params['tab'] != 'register') {
                $this->error(__('Unknown operation'));
            }
            $validate = new UserValidate();
            try {
                $validate->scene($params['tab'])->check($params);
            } catch (ValidateException $e) {
                $this->error($e->getMessage());
            }

            $captchaObj = new Captcha();
            if (!$captchaObj->check($params['captcha'], $params['captchaId'])) {
                $this->error(__('Please enter the correct verification code'));
            }

            if ($params['tab'] == 'login') {
                $res = $this->auth->login($params['username'], $params['password'], (bool)$params['keep']);
            } elseif ($params['tab'] == 'register') {
                $res = $this->auth->register($params['username'], $params['password'], $params['mobile'], $params['email']);
            }

            if ($res === true) {
                $this->success(__('Login succeeded!'), [
                    'userinfo'  => $this->auth->getUserInfo(),
                    'routeName' => 'user'
                ]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Check in failed, please try again or contact the website administrator~');
                $this->error($msg);
            }
        }

        $this->success('', [
            'accountVerificationType' => get_account_verification_type()
        ]);
    }

    public function logout()
    {
        if ($this->request->isPost()) {
            $refreshToken = $this->request->post('refresh_token', '');
            if ($refreshToken) Token::delete((string)$refreshToken);
            $this->auth->logout();
            $this->success();
        }
    }
}