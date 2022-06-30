<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * flbooth文章接口
 */
class Article extends Api
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    
	
    /**
     * 获取指定文章列表
     *
     * @ApiSummary  (flbooth 获取文章列表)
     * @ApiMethod   (POST)
	 * 
	 * @param string $type 文章类型
	 * @param string $list_rows 每页数量
	 */
    public function getList()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$type = $this->request->post('type');
			$where['status'] = 'normal';
			$config = get_addon_config('flbooth');
			if($type == 'help'){
				$where['category_id'] = $config['config']['help_category'];
			}
			if($type == 'new'){
				$where['category_id'] = $config['config']['new_category'];
			}
			if($type == 'sys'){
				$where['category_id'] = $config['config']['sys_category'];
			}
			$data = model('app\api\model\flbooth\Article')
				->where($where)
				->field('id,title,description,image,images,flag,views,created')
				->order('created desc')
				->paginate();
			
			$this->success('返回成功', $data);
		}
		$this->error(__('非法请求'));
        
    }
    
	
    /**
     * 获取内容详情
     *
     * @ApiSummary  (flbooth 获取内容详情)
     * @ApiMethod   (POST)
     * 
	 * @param string $id 文章ID
     */
    public function details()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$id = $this->request->get('id');
		$id ? $id : ($this->error(__('Invalid parameters')));
		$row = model('app\api\model\flbooth\Article')
			->where(['id' => $id])
			->find();
		// 1.0.5升级
		if(!$row){
			$this->error(__('没有找到任何内容'));
		}
		// 点击 +1
		$row->setInc('views');
		$this->success('返回成功', $row);
    }
    
	
	/**
	 * 获取广告详情
	 *
	 * @ApiSummary  (flbooth 获取内容详情)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $id 文章ID
	 */
	public function adDetails($id = null)
	{
		$row = model('app\api\model\flbooth\Advert')->get($id);
		// 1.0.5升级
		if(!$row){
			$this->error(__('没有找到任何内容'));
		}
		// 点击 +1
		$row->setInc('views');
		$this->success('返回成功', $row);
	}
	
}
