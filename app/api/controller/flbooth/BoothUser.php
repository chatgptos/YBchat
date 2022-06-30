<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 展商用户信息
 */
class BoothUser extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = [''];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2','getUserById'];


    /**
     * 展商用户信息
     *
     * @ApiTitle    (展商用户信息)
     * @ApiSummary  (展商用户信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="user_id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655882282","data":{"id":1,"title":"111","intro":"北海道崛起带来的后世界","start_time":1111,"end_time":0,"data":"11","template":"11","css":"11","topic_img":"11","title_pic":"11","base_style":"1","htmls":"1","keywords":"11","description":"11","start_time_text":"1970-01-01 08:18:31","end_time_text":"1970-01-01 08:00:00"}})

     */
    public function getUserById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\User::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }



}
