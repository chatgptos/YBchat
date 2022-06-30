<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * flbooth 反馈接口
 */
class Feedback extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
	    $this->model = new \app\api\model\flbooth\Feedback;
	}
	
	/**
	 * 反馈列表
	 */
	public function lists()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$list = $this->model
			->where('user_id', $this->auth->id)
			->order('created desc')
			->paginate();
		$this->success('ok',$list);
	}
	
    /**
     * 反馈新增、读取
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
