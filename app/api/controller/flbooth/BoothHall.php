<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 展厅接口
 */
class BoothHall extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['getHallById','getHallActivityGuideById','getHallServiceById','getHallAreaGuideById'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['test2'];


    /**
     * 展厅
     *
     * @ApiTitle    (展厅/展区)
     * @ApiSummary  (展厅/展区)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展厅/展区id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655878085","data":{"id":1,"exhibition_id":5,"hall_name":"2012中国国际渔业博览会海外展区","booths_num":0,"hall_map":".\/home\/img\/16558173804170.jpg","hall_addr":"2012中国国际渔业博览会海外展区","map_height":578,"map_width":845,"hall_namein":"A"}})

     */
    public function getHallById()
    {
        $booth_id = $this->request->post("id");

        $booth_info = \app\admin\model\Hall::where('id', $booth_id)->find();

        $this->success('', $booth_info);
    }




    /**
     * 展馆导览
     *
     * @ApiTitle    (展馆导览/展区)
     * @ApiSummary  (展馆导览/展区)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展厅/展区id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655878085","data":{"id":1,"exhibition_id":5,"hall_name":"2012中国国际渔业博览会海外展区","booths_num":0,"hall_map":".\/home\/img\/16558173804170.jpg","hall_addr":"2012中国国际渔业博览会海外展区","map_height":578,"map_width":845,"hall_namein":"A"}})

     */
    public function getHallAreaGuideById()
    {
        $exhibition_id = $this->request->post("id",0);//展会id  中食展,云选展

        $where['exhibition_id'] = $exhibition_id;
        $where=array_filter($where);


        $data['hall_list'] = model('app\admin\model\Hall')
            ->where($where)
            ->field('*')
            ->order('created desc')
            ->select();


        $where['is_recommend'] = 1;
        //获取推荐活动
        $recommend_exhibitor = \app\admin\model\Boothinfo::where($where)->select();
        $data['recommend_exhibitor']=$recommend_exhibitor;
        $this->success('', $data);
    }



    /**
     * 场馆服务
     *
     * @ApiTitle    (场馆服务)
     * @ApiSummary  (场馆服务)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="展会id 中食展1")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655878085","data":{"id":1,"exhibition_id":5,"hall_name":"2012中国国际渔业博览会海外展区","booths_num":0,"hall_map":".\/home\/img\/16558173804170.jpg","hall_addr":"2012中国国际渔业博览会海外展区","map_height":578,"map_width":845,"hall_namein":"A"}})

     */
    public function getHallServiceById()
    {
        $exhibition_id = $this->request->post("id",0);//展会id  中食展1,云选展2

        $where['id'] = $exhibition_id;
        $where=array_filter($where);


        $data = model('app\admin\model\Exhibition')
            ->where($where)
            ->field('*')
            ->order('created desc')
            ->find();
        $this->success('', $data);
    }



}
