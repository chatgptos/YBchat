<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;
use fast\Tree;
/**
 * flbooth展商店铺接口
 */
class Shop extends Api
{
    protected $noNeedLogin = ['getShopInfo'];
    protected $noNeedRight = ['*'];
	
	public function _initialize()
	{
	    parent::_initialize();
		$this->model = model('app\api\model\flbooth\Shop');
	}
	
	/**
	 * 获取展商店铺相关数据
     *
	 * @ApiSummary  (flbooth 获取店铺相关数据)
	 * @ApiMethod   (GET)
	 *
	 * @param string $id shop_id
	 */
	public function getShopInfo($id = null)
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		// 获取店铺信息
		$row = $this->model->get($id);
		if (!$row) {
		    $this->error(__('未找到此展商'));
		}
		// 获取商家类目
		$tree = Tree::instance();
		$tree->init(
			model('app\api\model\flbooth\ShopSort')
				->where(['shop_id' => $row['id']])
				->field('id, pid, name, image')
				->order('weigh asc')
				->select()
		);
		$row['category'] = $tree->getTreeArray(0);
		// 查看是否被关注
		$row['isFollow'] = model('app\api\model\flbooth\find\Follow')
			->where([
				'user_no' => $row['find_user']['user_no'], 
				'user_id' => $this->auth->id
			])
			->count();
		$row['isLive'] = model('app\api\model\flbooth\Live')
			->where(['shop_id' => $row['id'], 'state' => 1])
			->field('id')
			->find();
		// 获取类目样式配置
		$shopConfig = model('app\api\model\flbooth\ShopConfig')
			->where(['shop_id' => $row['id']])
			->find();
		//$row['categoryStyle'] = (int)$shopConfig['category_style'];
		// 获取商家自定义页面
		$row['page'] = model('app\api\model\flbooth\Page')
			->where([
				'shop_id' => $row['id'], 
				'type' => 'shop'
			])
			->field('id, name, page, item')
			->find();
		$this->success('返回成功', $row);
	}


    /**
     * 展商报名参展信息
     *
     * @ApiTitle    (展商报名参展信息)
     * @ApiSummary  (展商报名参展信息)
     * @ApiMethod   (POST)
     * @ApiParams   (name="name", type="integer", required=true, description="展商名称")
     * @ApiParams   (name="number", type="integer", required=true, description="身份证号码/纳税人识别号")
     * @ApiParams   (name="image", type="string", required=false, description="申请材料照片")
     * @ApiParams   (name="trademark", type="integer", required=false, description="备注")
     * @ApiParams   (name="wechat", type="integer", required=false, description="微信名称")
     * @ApiParams   (name="mobile", type="integer", required=true, description="手机号")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655882282","data":{"id":1,"title":"111","intro":"北海道崛起带来的后世界","start_time":1111,"end_time":0,"data":"11","template":"11","css":"11","topic_img":"11","title_pic":"11","base_style":"1","htmls":"1","keywords":"11","description":"11","start_time_text":"1970-01-01 08:18:31","end_time_text":"1970-01-01 08:00:00"}})

     */
	public function apply()
	{
	    //   (name="image", type="File", required=true, description="申请材料照片")
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$row = model('app\api\model\flbooth\Auth')
			->where(['user_id' => $this->auth->id])
			->find();
		if ($this->request->isPost()) {
			$params = $this->request->post();
//            var_dump($this->auth);
//            var_dump($params['file']);die;
//            die;
			$data = [
				'name' => $params['name'],
				'user_id' => $this->auth->id,
				'number' => $params['number'],
				'image' => $params['image'],
				'trademark' => $params['trademark'],
				'wechat' => $params['wechat'],
				'mobile' => $params['mobile'],
				'state' => 1
			];

			if($row){
				$row->save($data);
			}else{
				model('app\api\model\flbooth\Auth')->data($data)->save();
			}
			$this->success('返回成功', $params);
		}
		$this->success('返回成功', $row);
	}
}