<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 展位接口
 */
class Booth extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getBoothById', 'getBoothtemById', 'getBoothTypeById','getBoothHallById'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];

    /**
     * 展位接口
     *
     * @ApiTitle    (展位接口)
     * @ApiSummary  (获取展位信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展位id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655876160","data":{"id":4,"exhibition_id":5,"hall_id":47,"coordinate_x":600,"coordinate_y":3500,"booth_widht":200,"booth_height":300,"booth_area":"54㎡","booth_standard":"6m×9m","booth_num":"exHibition4","booth_name":"A101","sales_status":1,"booth_tips":"博尔塔拉三宝","company_name":"","category":".","country":".","state":".","addr":".","moble_phone":".","phone":".","email":".","webaddr":".","contacts":".","fax":".","position":".","booth_type":"1","open_angle":0,"booth_discount":0,"sales_distribution":".","booth_price":".","angle_add":".","booth_amount":".","build_state":".","china_abbreviate":"博尔塔拉三宝","english_abbreviate":"Bortala Sanbao","xiongk_num_free":14,"xiongk_num_change":14,"is_assigned":1,"imp_buyernum":3,"upload_batch":null}})

     */
    public function getBoothById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Boothinfo::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }



    /**
     * 展位接口
     *
     * @ApiTitle    (展位模板接口)
     * @ApiSummary  (获取展位模板信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展位模板id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655876160","data":{"id":4,"exhibition_id":5,"hall_id":47,"coordinate_x":600,"coordinate_y":3500,"booth_widht":200,"booth_height":300,"booth_area":"54㎡","booth_standard":"6m×9m","booth_num":"exHibition4","booth_name":"A101","sales_status":1,"booth_tips":"博尔塔拉三宝","company_name":"","category":".","country":".","state":".","addr":".","moble_phone":".","phone":".","email":".","webaddr":".","contacts":".","fax":".","position":".","booth_type":"1","open_angle":0,"booth_discount":0,"sales_distribution":".","booth_price":".","angle_add":".","booth_amount":".","build_state":".","china_abbreviate":"博尔塔拉三宝","english_abbreviate":"Bortala Sanbao","xiongk_num_free":14,"xiongk_num_change":14,"is_assigned":1,"imp_buyernum":3,"upload_batch":null}})

     */
    public function getBoothtemById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Boothtem::where('id', $booth_id)->find();
        $this->success('', $booth_info);
    }

    /**
     * 展位类型
     *
     * @ApiTitle    (展位类型)
     * @ApiSummary  (获取展位类型)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展位类型id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655876160","data":{"id":4,"exhibition_id":5,"hall_id":47,"coordinate_x":600,"coordinate_y":3500,"booth_widht":200,"booth_height":300,"booth_area":"54㎡","booth_standard":"6m×9m","booth_num":"exHibition4","booth_name":"A101","sales_status":1,"booth_tips":"博尔塔拉三宝","company_name":"","category":".","country":".","state":".","addr":".","moble_phone":".","phone":".","email":".","webaddr":".","contacts":".","fax":".","position":".","booth_type":"1","open_angle":0,"booth_discount":0,"sales_distribution":".","booth_price":".","angle_add":".","booth_amount":".","build_state":".","china_abbreviate":"博尔塔拉三宝","english_abbreviate":"Bortala Sanbao","xiongk_num_free":14,"xiongk_num_change":14,"is_assigned":1,"imp_buyernum":3,"upload_batch":null}})

     */
    public function getBoothTypeById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Boothtype::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }




}
