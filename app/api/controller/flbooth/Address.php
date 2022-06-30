<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
/**
 * flbooth地址接口
 */
class Address extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    
    
    /**
     * 获取地址列表
     *
     * @ApiSummary  (flbooth 地址接口获取地址列表)
     * @ApiMethod   (GET)
	 * 
	 */
    public function getaddress()
    {
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$list = model('app\api\model\flbooth\Address')
			->where('user_id', $this->auth->id)
			->field('id,user_id,adcode,address,address_name,city,citycode,country,default,district,formatted_address,location,mobile,name,province')
			->order('modified desc')
			->paginate();
		$this->success('返回成功', $list);
    }
    
    /**
     * 修改/新增地址
     *
     * @ApiSummary  (flbooth 地址接口修改/新增地址)
     * @ApiMethod   (POST)
	 * 
	 * @param string $user_id 用户ID
	 */
    public function address()
    {
        if ($this->request->isPost()) {
			//设置过滤方法
			$this->request->filter(['strip_tags']);
        	$request = $this->request->post();
        	$address = new \app\api\model\flbooth\Address();
        	$data = $request['data'];
        	$data['user_id'] = $this->auth->id;
        	$count = $address->where(['user_id'=>$data['user_id']])->count();
			// 操作        	
        	switch ($request['type']) {
				case "edit": 
					if($count <= 1){
						$data['default'] = 1;
						$address->allowField(true)->save($data,['id' => $data['id']]);
						$this->success('ok','成功(仅一个不许修改默认)');
					}else{
						// 更新
						$address->allowField(true)->save($data,['id' => $data['id']]);
						// 单独设置默认，避免非默认消耗资源
						if($data['default'] == 1){
							$list = \app\api\model\flbooth\Address::all(['user_id'=>$data['user_id']]);
							$list = collection($list)->toArray();
							$itemdata = [];
							foreach($list as $item){
							    if($item['id'] == $data['id']){
							    	$item['default'] = 1;
							    }else{
							    	$item['default'] = 0;
							    }
							    $itemdata[] = $item;
							}
							$address->allowField(true)->saveAll($itemdata);
						}
						$this->success('ok');
					}
					break;
				case "add": 
					if($count == 0){
						// 新增
						$data['default'] = 1;
						$address->data($data);
						$address->save();
						$this->success('地址回调',$address);
					}else{
						$address->data($data);
						$address->save();
						if($data['default'] == 1){
							$list = \app\api\model\flbooth\Address::all(['user_id'=>$data['user_id']]);
							$list = collection($list)->toArray();
							$itemdata = [];
							foreach($list as $item){
							    if($item['id'] == $address->id){
							    	$item['default'] = 1;
							    }else{
							    	$item['default'] = 0;
							    }
							    $itemdata[] = $item;
							}
							$address->saveAll($itemdata);
						}
						$this->success('新增成功',[]);		
					}
					break;
			}
		} else {
		    $this->error(__('非法请求'));
		}
    }
	
    /**
     * 删除地址
     *
     * @ApiSummary  (flbooth 地址接口删除地址)
     * @ApiMethod   (POST)
	 * 
	 * @param string $id 地址ID
	 */
    public function deladdress()
    {
        if ($this->request->isPost()) {
			//设置过滤方法
			$this->request->filter(['strip_tags']);
        	$id = $this->request->post('id');
        	if (!$id) {
	            $this->error(__('Invalid parameters'));
	        }
        	if(model('app\api\model\flbooth\Address')->where(['id'=>$id,'user_id'=>$this->auth->id])->delete()){
        		$this->success(__('删除成功',[]));
        	}else{
        		$this->error(__('删除失败'));
        	}
		} else {
		    $this->error(__('非法请求'));
		}
    }
}
