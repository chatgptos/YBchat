<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 活动主题
 */
class BoothActivity extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getActivityById'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];


    /**
     * 活动接口
     *
     * @ApiTitle    (活动接口)
     * @ApiSummary  (活动接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="活动id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655881227","data":{"id":1,"shop_id":1,"freight":"1","iscloud":"0","isauto":"0","secret":null,"key":null,"partner_id":null,"partner_key":null,"siid":null,"tempid":null,"welcome":"你好欢迎到店铺","send_name":"杨林","send_phone_num":"13236390680","send_addr":"深圳市福田区车公庙泰然家园","return_name":"杨林","return_phone_num":"13236390680","return_addr":"深圳市福田区车公庙泰然家园","created":1616935663,"modified":1617506640,"freight_text":"Freight 1","iscloud_text":"Iscloud 0","isauto_text":"Isauto 0"}})

     */
    public function getActivityById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Activity::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }



}
