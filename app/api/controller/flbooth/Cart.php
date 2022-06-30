<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;

/**
 * flbooth购物车接口
 */
class Cart extends Api
{
    protected $noNeedLogin = [];
	protected $noNeedRight = ['*'];
    
	public function _initialize()
	{
	    parent::_initialize();
	    $this->model = new \app\api\model\flbooth\Cart;
	}
	
	/**
	 * 获取或合并购物车
	 *
	 * @ApiSummary  (flbooth 购物车接口获取或合并购物车)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $cart 本地购物车数据
	 */
	public function synchro()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$post = $this->request->post();
			$user_id = $this->auth->id;
			// 如果存在合并购物车，返回；否则获取购物车
			if($post['cart']){
			    $newlist = [];
				foreach($post['cart'] as $row){
					// 1.1.2升级 排除失效商品
					if(model('app\api\model\flbooth\Goods')->get($row['goods_id'])){
						$where = [
							'goods_id' => $row['goods_id'],
							'shop_id' => $row['shop_id'],
							'sku_id' => $row['sku_id'],
							'user_id' => $user_id
						];
						$cart = $this->model->where($where)->find();
						if(!$cart){
							// 局部写入 1.0.2升级
							$where['number'] = $row['number'];
						    $newlist[] = $where;
						}
					}
				}
				if(count($newlist) > 0){
				    $this->model->saveAll($newlist, false);
				}
			}
			
			// 查询购物车最新商品详情 1.0.2升级
			$list = [];
			foreach ($this->model->where('user_id', $user_id)->select() as $vo) {
				$sku = $vo->suk; //1.0.3升级 很诡异的问题命名sku和会产生冲突
				// 查询是否还有库存
				if($sku['stock'] > 0){
					$shop = $vo->shop;
					$goods = $vo->goods;
					if($goods){
						$list[] = [
							'shop_id' => $shop['id'],
							'shop_name' => $shop['shopname'],
							'goods_id' => $goods['id'],
							'title' => $goods['title'],
							'image' => $goods['image'],
							'number' => $vo['number'],
							'sku_id' => $vo['sku_id'],
							'sku' => $sku,
							'sum' => bcmul($sku['price'], $vo['number'], 2),
							'checked' => false,
							
						];
					}
				}
			}	
			$this->success('返回成功', $list);
		}
		$this->error(__('非正常请求'));
	}
	
	/**
	 * 操作购物车数据库
	 *
	 * @ApiSummary  (flbooth 购物车接口操作购物车数据库)
	 * @ApiMethod   (POST)
	 * 
	 * @param string $type 操作方式
	 * @param string $data 改变数据
	 */
	public function storage()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$post = $this->request->post();
			$user_id = $this->auth->id;
			$return = '';
			// 清空购物车
			if($post['type'] == 'empty'){
			    $this->model->where(['user_id' => $this->auth->id])->delete();
			// 新增购物车
			}else if($post['type'] == 'add'){
			    $row = $post['data'];
			    $where = [
					'goods_id' => $row['goods_id'],
					'shop_id' => $row['shop_id'],
					'sku_id' => $row['sku_id'],
					'user_id' => $user_id
				];
				// 查询是否已存在，如果已存在只改变数量和总价
				$cart = $this->model->where($where)->find();
			    if($cart){
			        $number = $cart['number'] + $row['number'];
    				$params = [
    					'number' => $number,
    					//1.0.5升级 'sum' => bcmul($cart['sku']['price'], $number)
    				];
    				$cart->save($params);
				}else{
					// 只新增ID，1.0.2升级
					$where['number'] = $row['number'];
				    $this->model->save($where, false);
				}
			// 新增购物车
			}else if($post['type'] == 'bcsub' || $post['type'] == 'bcadd'){
				$where = [
					'goods_id' => $post['goods_id'],
					'sku_id' => $post['sku_id'],
					'user_id' => $user_id
				];
				$cart = $this->model->where($where)->find();
				// 1.0.5升级
				$cart->save(['number' => $post['number']]);
			// 批量删除
			}else if($post['type'] == 'del'){	
				foreach ($post['data'] as $row) {
		            $where = [
    					'goods_id' => $row['goods_id'],
    					'sku_id' => $row['sku_id'],
    					'user_id' => $user_id
    				];
                    $this->model->where($where)->delete();
                }
			// 先将传来的批量写进关注表，在删除这些
			}else if($post['type'] == 'follow'){
			    $follow = [];
				foreach ($post['data'] as $row) {
		            $where = [
    					'goods_id' => $row['goods_id'],
    					'sku_id' => $row['sku_id'],
    					'user_id' => $user_id
    				];
    				$follow[] = [
    				    'user_id' => $user_id,
    				    'goods_id' => $row['goods_id']
    				];
                    $this->model->where($where)->delete();
                }
                $follow = array_unique($follow, SORT_REGULAR);
                $return = model('app\api\model\flbooth\GoodsFollow')->saveAll($follow, false);
                $return = count($return);
			}else{
			    $this->error(__('网络繁忙'));
			}
			$this->success('更新购物车完成！', $return);
		}
		$this->error(__('非正常请求'));
	}
}
