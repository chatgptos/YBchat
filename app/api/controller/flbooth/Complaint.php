<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * flbooth 投诉举报接口
 */
class Complaint extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
	    $this->model = new \app\api\model\flbooth\Complaint;
	}
	
	/**
	 * 投诉举报列表
	 */
	public function lists()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$list = $this->model
			->where('user_id', $this->auth->id)
			->order('created desc')
			->paginate();
		foreach ($list as $row) {
			// 举报类型:0=用户举报,1=商品举报,2=店铺举报,3=营销活动-拼团举报
			if($row['type'] === '1'){
				$row->goods ? $row->goods->visible(['id','title','image','price']) : [];
			}
			if($row['type'] === '3'){
				$row->groups ? $row->groups->visible(['id','title','image','price']) : [];
			}
		}
		$this->success('ok',$list);
	}
    
    /**
     * 举报新增、读取
     */
    public function add()
    {
    	//设置过滤方法
    	$this->request->filter(['strip_tags']);
    	if ($this->request->isPost()) {
    		$params = $this->request->post();
    		$params['user_id'] = $this->auth->id;
    		$data = $this->model->allowField(true)->save($params);
    		$data? $this->success('ok',$data) : $this->error(__('服务器繁忙'));
    	}
    	$list = $this->model
    		->where('user_id', $this->auth->id)
    		->order('created desc')
    		->paginate();
    	$this->success('ok',$list);
    }
    
}
