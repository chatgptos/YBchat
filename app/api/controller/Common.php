<?php

namespace app\api\controller;

use ba\Captcha;
use ba\Random;
use app\common\facade\Token;
use app\common\controller\Api;

/**
 * 公共
 */
class Common extends Api
{

    /**
     * 验证码（请直接再浏览器访问url）
     *
     * @ApiTitle    （验证码-请直接浏览器访问url，图片不显示,前端加载src地址）
     * @ApiSummary  (验证码-)
     * @ApiRoute    (/api/common/captcha/{id})
     * @ApiMethod   (POST)
     * @ApiParams  (name=id, type=string, required=true, description="唯一标识")    * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({
     * 'code':'1',
     * 'msg':'返回成功'
     * })
     */
    public function captcha()
    {
        $captchaId = $this->request->request('id');
        $config    = array(
            'codeSet'  => '123456789',            // 验证码字符集合
            'fontSize' => 22,                     // 验证码字体大小(px)
            'useCurve' => false,                  // 是否画混淆曲线
            'useNoise' => true,                   // 是否添加杂点
            'length'   => 4,                      // 验证码位数
            'bg'       => array(255, 255, 255),   // 背景颜色
        );

        $captcha = new Captcha($config);
        return $captcha->entry($captchaId);
    }

    public function refreshToken()
    {
        $refreshToken = $this->request->post('refresh_token');
        $refreshToken = Token::get($refreshToken);

        if (!$refreshToken || $refreshToken['expiretime'] < time()) {
            $this->error(__('Login expired, please login again.'));
        }

        $newToken = Random::uuid();

        if ($refreshToken['type'] == 'admin-refresh') {
            $baToken = $this->request->server('HTTP_BATOKEN', $this->request->request('batoken', ''));
            if (!$baToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baToken);
            Token::set($newToken, 'admin', $refreshToken['user_id'], 86400);
        } elseif ($refreshToken['type'] == 'user-refresh') {
            $baUserToken = $this->request->server('HTTP_BA_USER_TOKEN', $this->request->request('ba-user-token', ''));
            if (!$baUserToken) {
                $this->error(__('Invalid token'));
            }
            Token::delete($baUserToken);
            Token::set($newToken, 'user', $refreshToken['user_id'], 86400);
        }

        $this->success('', [
            'type'  => $refreshToken['type'],
            'token' => $newToken
        ]);
    }
}