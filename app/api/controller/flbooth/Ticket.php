<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * 门票
 */
class Ticket extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
	
    /**
     * 获取门票列表
     *
     * @ApiSummary  (flbooth 获取门票列表)
     * @ApiMethod   (POST)
     * @ApiTitle    (获取门票列表)
     * @ApiSummary  (获取门票列表)
     * @param string $type 门票类型
     * @param string $status 门票类型
     * @param string $list_rows 每页数量
     * @ApiParams   (name="type", type="integer", required=false, description="type")
     * @ApiParams   (name="status", type="integer", required=true, description="1有效 2全部")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655881227","data":{"id":1,"shop_id":1,"freight":"1","iscloud":"0","isauto":"0","secret":null,"key":null,"partner_id":null,"partner_key":null,"siid":null,"tempid":null,"welcome":"你好欢迎到店铺","send_name":"杨林","send_phone_num":"13236390680","send_addr":"深圳市福田区车公庙泰然家园","return_name":"杨林","return_phone_num":"13236390680","return_addr":"深圳市福田区车公庙泰然家园","created":1616935663,"modified":1617506640,"freight_text":"Freight 1","iscloud_text":"Iscloud 0","isauto_text":"Isauto 0"}})
     */
    public function getList()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$type = (int) $this->request->post('type',0);// exhibition 1  topic2
            $status = (int) $this->request->post('status',0);//  1正常  2过期

            $where['user_id'] = $this->auth->id;
			$where['status'] = $status;
            $where['type'] = $type;
            $where=array_filter($where);

			$data['ticket_list'] = model('app\admin\model\Ticket')
				->where($where)
				->field('*')
				->order('apply_time desc')
				->select();


            //获取推荐活动
            $recommend_activity = \app\admin\model\Activity::where('is_recommend', 1)->find();
            $data['recommend_activity']=$recommend_activity;
            //获取推荐活动
            $recommend_topic = \app\admin\model\Topic::where('is_recommend', 1)->find();
            $data['recommend_topic']=$recommend_topic;

			$this->success('返回成功', $data);
		}
		$this->error(__('非法请求'));
        
    }
    
	
    /**
     * 获取门票详情
     *
     * @ApiSummary  (flbooth 获取门票详情)
     * @ApiMethod   (POST)
     * 
	 * @param string $id 门票ID
     */
    public function details()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->post('id');
		$id ? $id : ($this->error(__('Invalid parameters')));
		$row = model('app\admin\model\Ticket')
			->where(['id' => $id])
			->find();

		//
		if(!$row){
			$this->error(__('没有找到任何内容'));
		}
		// 点击 +1

//		$row->setInc('views');
		$this->success('返回成功', $row);
    }
    
	
	/**
	 * 获取广告详情
	 *
	 * @ApiSummary  (flbooth 获取内容详情)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 广告ID
	 */
	public function adDetails($id = null)
	{
		$row = model('app\admin\model\Advert')->get($id);
		//
		if(!$row){
			$this->error(__('没有找到任何内容'));
		}
		// 点击 +1
//		$row->setInc('views',1);
		$this->success('返回成功', $row);
	}
	
}
