<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 商家店铺接口
 */
class BoothShop extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getShopById'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];


    /**
     * 商家店铺
     *
     * @ApiTitle    (商家店铺)
     * @ApiSummary  (获取商家店铺)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="商家店铺id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655880878","data":{"id":1,"user_id":2,"shopname":" ","keywords":" ","description":" ","service_ids":"1,2,4,5","avatar":"\/uploads\/20210328\/2b78bc80b6b827f780dafc638986206b.jpeg","state":"0","level":0,"islive":1,"isself":0,"bio":" 付费用户2w+，会员用户10w+，我们用智能科技贯穿线上线下，向企业开放资源，为用户提供源头优质产品","city":"江苏省\/南京市\/雨花台区","return":"","like":4,"score_describe":0,"score_service":0,"score_deliver":0,"score_logistics":0,"weigh":0,"verify":"3","created":null,"modified":1616935536,"deleted":null,"status":"normal","state_text":"State 0","verify_text":"Verify 3","status_text":"正常"}})

     */
    public function getShopById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Shop::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }



}
