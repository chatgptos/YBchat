<?php

namespace app\api\controller\flbooth;

use app\common\controller\Api;
use think\addons\Service;
use fast\Random;
use fast\Tree;
use think\Config;
use think\Db;
use think\Hook;

/**
 * flbooth公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = ['index','init','category','search','update', 'area', 'adverts','searchList','setSearch','about', 'thumbnail'];
	protected $noNeedRight = ['*'];

//
//    /**
//     * 首页
//     *
//     * @ApiSummary  (flbooth 获取首页、应用、展商)
//     * @ApiMethod   (GET)
//     *
//     */
//    public function index()
//    {
//        $cacheTime = 60; //   查询缓存
//
//        $booth_id=1;
//        $article_id=1;
//
//        //banner 活动图
//        $activity = \app\admin\model\Activity::where('id', $booth_id)->find();
//        $activity['activity_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
//        //
//        $article = model('app\api\model\flbooth\Article')
//            ->where(['id' => $article_id])
//            ->find();
//
//
//        $Exhibitor = model('app\admin\model\Exhibitor')
//            ->field('*')
//            ->order('edit_time desc')
//            ->paginate(3,0)->toArray();
//
//
//
//        $homeModules=
//        array(
//        'items'=>array(
//           array(
//            'name'=>'电子门票',
//               "type"=> "ticket",
//                   "style"=>array(
//                        "color"=>"#000000",
//                         "margin"=> "8px 12.5px 0 12.5px",
//                         "border-radius"=> "10px",
//                         "overflow"=>"hidden"   ),
//                 "params"=>array(
//                   "interval"=>"2800",
//                   "height"=> "115px",
//                   "banstyle"=> "1",
//                   "overflow"=>"hidden"
//               ),
//            ),
//            array(
//                'name'=>'场馆导览',
//                "type"=> "guide",
//                "style"=>array(
//                    "color"=>"#000000",
//                    "margin"=> "8px 12.5px 0 12.5px",
//                    "border-radius"=> "10px",
//                    "overflow"=>"hidden"   ),
//                "params"=>array(
//                    "interval"=>"2800",
//                    "height"=> "115px",
//                    "banstyle"=> "1",
//                    "overflow"=>"hidden"
//                ),
//            ),
//            array(
//                'name'=>'合作洽谈',
//                "type"=> "cooperation",
//                "style"=>array(
//                    "color"=>"#000000",
//                    "margin"=> "8px 12.5px 0 12.5px",
//                    "border-radius"=> "10px",
//                    "overflow"=>"hidden"   ),
//                "params"=>array(
//                    "interval"=>"2800",
//                    "height"=> "115px",
//                    "banstyle"=> "1",
//                    "overflow"=>"hidden"
//                ),
//            ),
//            array(
//                'name'=>'场馆服务',
//                "type"=> "service",
//                "style"=>array(
//                    "color"=>"#000000",
//                    "margin"=> "8px 12.5px 0 12.5px",
//                    "border-radius"=> "10px",
//                    "overflow"=>"hidden"   ),
//                "params"=>array(
//                    "interval"=>"2800",
//                    "height"=> "115px",
//                    "banstyle"=> "1",
//                    "overflow"=>"hidden"
//                ),
//            ),
//            array(
//                'name'=>'名片夹',
//                "type"=> "card",
//                "style"=>array(
//                    "color"=>"#000000",
//                    "margin"=> "8px 12.5px 0 12.5px",
//                    "border-radius"=> "10px",
//                    "overflow"=>"hidden"   ),
//                "params"=>array(
//                    "interval"=>"2800",
//                    "height"=> "115px",
//                    "banstyle"=> "1",
//                    "overflow"=>"hidden"
//                ),
//            ), )
//        );
//        $modulesData  = [
//            "banner" => $activity,
//            "article" => $article,
//            "homeModules" => $homeModules,
//            "exhibitors" => $Exhibitor['data'],
//        ];
//        $this->success('', $modulesData);
//    }





    /**
     * 活动扫码-入口接口
     *
     * @ApiTitle    (活动扫码-入口接口)
     * @ApiSummary  (活动扫码-入口接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="活动id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655881227","data":{"id":1,"shop_id":1,"freight":"1","iscloud":"0","isauto":"0","secret":null,"key":null,"partner_id":null,"partner_key":null,"siid":null,"tempid":null,"welcome":"你好欢迎到店铺","send_name":"杨林","send_phone_num":"13236390680","send_addr":"深圳市福田区车公庙泰然家园","return_name":"杨林","return_phone_num":"13236390680","return_addr":"深圳市福田区车公庙泰然家园","created":1616935663,"modified":1617506640,"freight_text":"Freight 1","iscloud_text":"Iscloud 0","isauto_text":"Isauto 0"}})

     */
    public function getActivityById()
    {
        $activity_id = $this->request->post("id");


        //获取介绍
        $activity = \app\admin\model\Activity::where('id', $activity_id)->find();
        //获取导航等信息
        $activity['hall_area'] ='20000';
        $activity['exhibitor_num'] ='2500';
        $activity['viewer_num'] ='150000';
        $activity['meetting_num'] ='1000';



        //合作伙伴/合作媒体/关于我们/联系我们/关注我们
        $activity['cooperative_partner_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        $activity['cooperative_media_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        $activity['about_us'] ='简介：蜂雷是科权科技（上海）有限公司旗下服务于个体创业的一款网络购物手机软件。开创新型模式，提供商品资源，获得销售商进货的低价位，
                                 减少厂商与消费者之间中间环节的消耗。联系我们：021-57630866 关注我们<img src="http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png"> ';


        //获取主题
        $activity['theme'] = model('app\admin\model\Theme')::where('act_id', $activity_id)
            ->field('*')
            ->paginate(1,8)->toArray();
        $activity['theme_num'] =count($activity['theme']['data']);
        $activity['theme'] =$activity['theme']['data'];
        $activity['activity_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        //获取峰会
        $activity['topic'] = model('app\admin\model\Topic')::where('act_id', $activity_id)
            ->field('*')
            ->paginate(1,8)->toArray();
        $activity['topic_num'] =count($activity['topic']['data']);
        $activity['topic'] =$activity['topic']['data'];



        //获取新闻
        $type = 'new';
        $where['status'] = 'normal';
        $config = get_addon_config('flbooth');
        if($type == 'exhibition'){
            $where['category_id'] = $config['config']['exhibition_category'];
        }
        if($type == 'new'){
            $where['category_id'] = $config['config']['new_category'];
        }
        if($type == 'sys'){
            $where['category_id'] = $config['config']['sys_category'];
        }
        $activity['article_new'] = model('app\api\model\flbooth\Article')
            ->where($where)
            ->field('id,title,description,image,images,flag,views,created')
            ->order('created desc')
            ->paginate()->toArray();
        $activity['article_num_new']=count($activity['article_new']['data']);
        $activity['article_new'] =$activity['article_new']['data'];


        //获取新闻
        $type = 'exhibition';
        $where['status'] = 'normal';
        $config = get_addon_config('flbooth');
        if($type == 'exhibition'){
            $where['category_id'] = $config['config']['exhibition_category'];
        }
        if($type == 'new'){
            $where['category_id'] = $config['config']['new_category'];
        }
        if($type == 'sys'){
            $where['category_id'] = $config['config']['sys_category'];
        }
        $activity['article_exhibition'] = model('app\api\model\flbooth\Article')
            ->where($where)
            ->field('id,title,description,image,images,flag,views,created')
            ->order('created desc')
            ->paginate()->toArray();
        $activity['article_num_exhibition']=count($activity['article_exhibition']['data']);
        $activity['article_exhibition'] =$activity['article_exhibition']['data'];


        $this->success('', $activity);
    }





    /**
     * 获取电子门票-入口接口
     *
     * @ApiTitle    (获取电子门票-入口接口)
     * @ApiSummary  (获取电子门票-入口接口)
     * @ApiMethod   (POST)
     * @ApiParams   (name="id", type="integer", required=true, description="门票id")
     * @ApiReturnParams   (name="code", type="integer", required=true, sample="0")
     * @ApiReturnParams   (name="msg", type="string", required=true, sample="返回成功")
     * @ApiReturnParams   (name="data", type="object", sample="{'user_id':'int','user_name':'string','profile':{'email':'string','age':'integer'}}", description="扩展数据返回")
     * @ApiReturn   ({"code":1,"msg":"","time":"1655881227","data":{"id":1,"shop_id":1,"freight":"1","iscloud":"0","isauto":"0","secret":null,"key":null,"partner_id":null,"partner_key":null,"siid":null,"tempid":null,"welcome":"你好欢迎到店铺","send_name":"杨林","send_phone_num":"13236390680","send_addr":"深圳市福田区车公庙泰然家园","return_name":"杨林","return_phone_num":"13236390680","return_addr":"深圳市福田区车公庙泰然家园","created":1616935663,"modified":1617506640,"freight_text":"Freight 1","iscloud_text":"Iscloud 0","isauto_text":"Isauto 0"}})

     */
    public function getTicketById()
    {
        $activity_id = $this->request->post("id");


        //获取介绍
        $activity = \app\admin\model\Activity::where('id', $activity_id)->find();
        //获取导航等信息
        $activity['hall_area'] ='20000';
        $activity['exhibitor_num'] ='2500';
        $activity['viewer_num'] ='150000';
        $activity['meetting_num'] ='1000';



        //合作伙伴/合作媒体/关于我们/联系我们/关注我们
        $activity['cooperative_partner_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        $activity['cooperative_media_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        $activity['about_us'] ='简介：蜂雷是科权科技（上海）有限公司旗下服务于个体创业的一款网络购物手机软件。开创新型模式，提供商品资源，获得销售商进货的低价位，
                                 减少厂商与消费者之间中间环节的消耗。联系我们：021-57630866 关注我们<img src="http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png"> ';


        //获取主题
        $activity['theme'] = model('app\admin\model\Theme')::where('act_id', $activity_id)
            ->field('*')
            ->paginate(1,8)->toArray();
        $activity['theme_num'] =count($activity['theme']['data']);
        $activity['theme'] =$activity['theme']['data'];
        $activity['activity_img'] ='http://flshop.com//assets/addons/flshop/img/show/main_bg3x.png';
        //获取峰会
        $activity['topic'] = model('app\admin\model\Topic')::where('act_id', $activity_id)
            ->field('*')
            ->paginate(1,8)->toArray();
        $activity['topic_num'] =count($activity['topic']['data']);
        $activity['topic'] =$activity['topic']['data'];



        //获取新闻
        $type = 'new';
        $where['status'] = 'normal';
        $config = get_addon_config('flbooth');
        if($type == 'exhibition'){
            $where['category_id'] = $config['config']['exhibition_category'];
        }
        if($type == 'new'){
            $where['category_id'] = $config['config']['new_category'];
        }
        if($type == 'sys'){
            $where['category_id'] = $config['config']['sys_category'];
        }
        $activity['article_new'] = model('app\api\model\flbooth\Article')
            ->where($where)
            ->field('id,title,description,image,images,flag,views,created')
            ->order('created desc')
            ->paginate()->toArray();
        $activity['article_num_new']=count($activity['article_new']['data']);
        $activity['article_new'] =$activity['article_new']['data'];


        //获取新闻
        $type = 'exhibition';
        $where['status'] = 'normal';
        $config = get_addon_config('flbooth');
        if($type == 'exhibition'){
            $where['category_id'] = $config['config']['exhibition_category'];
        }
        if($type == 'new'){
            $where['category_id'] = $config['config']['new_category'];
        }
        if($type == 'sys'){
            $where['category_id'] = $config['config']['sys_category'];
        }
        $activity['article_exhibition'] = model('app\api\model\flbooth\Article')
            ->where($where)
            ->field('id,title,description,image,images,flag,views,created')
            ->order('created desc')
            ->paginate()->toArray();
        $activity['article_num_exhibition']=count($activity['article_exhibition']['data']);
        $activity['article_exhibition'] =$activity['article_exhibition']['data'];


        $this->success('', $activity);
    }
	/**
	 * 一次性加载
	 *
	 * @ApiSummary  (flbooth 获取首页、购物车、类目数据)
	 * @ApiMethod   (GET)
	 *
	 */
    public function init()
    {
		$cacheTime = 60; // 1.1.2升级 查询缓存
		// 首页
		$homeList = model('app\api\model\flbooth\Page')
			->where('type','index')
			->cache(true, $cacheTime)
			->field('page, item')
			->find();			
		if(!$homeList){
			$this->error(__('尚未添加首页，请到后台【页面管理】添加首页'));
		}
		// 类目
		$tree = Tree::instance();
		$tree->init(model('app\api\model\flbooth\Category')
			->where(['type' => 'goods', 'isnav' => 1])
			->cache(true, $cacheTime)
			->field('id, pid, name, image')
			->order('weigh asc')
			->select());
		// 搜索关键字
		$searchList = model('app\api\model\flbooth\Search')
			->where(['flag' => 'index'])
			->field('keywords')
			->order('views desc')
			->limit(10)
		    ->select();
		// 获取配置
		$config = get_addon_config('flbooth');
		// 一次性获取模块
		$modulesData  = [
			"homeModules" => $homeList,
			"categoryModules" => $tree->getTreeArray(0),
			"searchModules" => $searchList
		];
		// 追加h5地址用于分享二维码等
		$config['config']['domain'] = $config['h5']['domain'].($config['h5']['router_mode'] == 'hash' ? '/#':'');
		// 输出
		$this->success('返回成功', [
			"modulesData" => $modulesData,
			"appStyle" => $config['style'],
			"appConfig" => $config['config']
		]);
    }
	
	/**
	 * 加载类目页
	 *
	 * @param string $id    类目ID
	 */
	public function category()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$category_id = implode(',', array_column(Tree::instance()->init(model('app\api\model\flbooth\Category')->cache(true, 60)->select())->getChildren($this->request->get('id'), true), 'id'));
		// 商品
		$goods = model('app\api\model\flbooth\Goods')
			->where('category_id', 'in', $category_id)
			->where('status', 'normal')
			->orderRaw('rand()')
			->paginate();
		foreach ($goods as $row) {
			$row->shop->visible(['state','shopname']);
			$row->isLive = model('app\api\model\flbooth\Live')->where(['shop_id' => $row['shop_id'], 'state' => 1])->field('id')->find();
		}
		// 营销活动-拼团
		$groups = model('app\api\model\flbooth\groups\Goods')
			->where('category_id', 'in', $category_id)
			->where('status', 'normal')
			->orderRaw('rand()')
			->select();
		$list = [
			'goods' => $goods,
			'seckill' => [],
			'groups' => $groups
		];
		$this->success('返回成功', $list);
	}
	
	/**
	 * APP热更新
	 *
	 * @ApiSummary  (flbooth APP热更新)
	 * @ApiMethod   (GET)
	 *
	 */
	public function update()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$row = model('app\api\model\flbooth\Version')
			->order('versionCode desc')
			->find();
		$this->success('返回成功', $row);	
	}
	
	/**
	 * 获取后端地址
	 *
	 * @ApiSummary  (flbooth APP热更新)
	 * @ApiMethod   (GET)
	 *
	 */
	public function area()
	{
		$tree = [];
		$packData = [];
		// 把数组的引用赋给新数组
		foreach (collection(model('app\common\model\Area')->field('id,pid,name,level')->select())->toArray() as $vo) {
			$packData[$vo['id']] = $vo;
		}
		// 获取树状结构的数组
		foreach ($packData as $key => $val) {
		    if ($val['pid'] == 0) 
				$tree[] = &$packData[$key]; 
			else 
				if($val['level'] === 3){
					$packData[$val['pid']]['area'][] = &$packData[$key];
				}else{
					$packData[$val['pid']]['city'][] = &$packData[$key];
				}
		}
		$this->success('返回成功', $tree);	
	}
	
	
	/**
	 * 加载广告
	 *
	 * @ApiSummary  (flbooth 加载广告)
	 * @ApiMethod   (GET)
	 *
	 */
	public function adverts()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$data = [
			'openAdverts' => [],
			'pageAdverts' => [],
			'categoryAdverts' => [],
			'firstAdverts' => [],
			'otherAdverts' => []
		];
		$list = model('app\api\model\flbooth\Advert')
			->field('id,category_id,media,module,type,url')
			->select();
		foreach ($list as $value) {
			$category_id = $value['category_id'];
			unset($value['category_id']);
			if($value['module'] == 'open'){
				$openData[] = $value;
				$data['openAdverts'] = $openData[array_rand($openData,1)];
			}
			if($value['module'] == 'page'){
				$data['pageAdverts'][] = $value;
			}
			if($value['module'] == 'category'){
				$data['categoryAdverts'][$category_id][] = $value;
			}
			if($value['module'] == 'first'){
				$data['firstAdverts'][] = $value;
			}
			if($value['module'] == 'other'){
				$data['otherAdverts'][] = $value;
			}
		}
		// 如果syspopup存在客户端将弹窗, 用于开启后通知用户
		$syspopup = '';
		// 通过大版本号查询，对应数据，未来版本升级开发
		$version = $this->request->request("version", '');
		$this->success('返回成功', ['data' => $data, 'version' => $version, 'syspopup' => $syspopup]);	
	}
	
	/**
	 * 热门搜索
	 *
	 * @ApiSummary  (flbooth 搜索关键词列表)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function searchList()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$list = model('app\api\model\flbooth\Search')
			->field('id,keywords,flag')
			->order('views desc')
			->limit(20)
		    ->select();
		$this->success('返回成功', $list);	
	}
	
	/**
	 * 提交搜索关键字给系统
	 *
	 * @ApiSummary  (flbooth 搜索关键词列表)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $keywords 关键字
	 */
	public function setSearch()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		$keywords = $this->request->request("keywords", '');
		$model = model('app\api\model\flbooth\Search');
		if($model->where('keywords',$keywords)->count() > 0){
			$model->where('keywords',$keywords)->setInc('views');
		}else{
			$model->save(['keywords'=>$keywords]);
		}
		$this->success('返回成功');	
	}
	
    /**
     * 实时搜索类目&相关类目
     *
     * @ApiSummary  (flbooth 搜索关键词列表)
     * @ApiMethod   (GET)
     * 
	 * @param string $search 搜索内容
     */
    public function search()
    {
    	//设置过滤方法
    	$this->request->filter(['strip_tags']);
		$search = $this->request->request('search', '');
		if($search){
			// 查询相关类目
			$categoryList = model('app\api\model\flbooth\Category')
			    ->where('name','like','%'.$search.'%')
				->field('id,name')
				->limit(20)
			    ->select();
				
			// 查询搜索数据
			$searchList = model('app\api\model\flbooth\Search')
			    ->where('keywords','like','%'.$search.'%')
				->field('keywords')
				->limit(20)
			    ->select();
			$result = array("categoryList" => $categoryList, "searchList" => $searchList);
			$this->success('返回成功', $result);	
		}else{
			$this->success('请输入关键字');
		}
    }
	
	/**
	 * 二维码配置
	 *
	 * @ApiSummary  (flbooth 查询二维码配置)
	 * @ApiMethod   (POST)
	 *
	 */
	public function qrcode()
	{
		//设置过滤方法
		$this->request->filter(['strip_tags']);
		if ($this->request->isPost()) {
			$list = model('app\api\model\flbooth\Qrcode')
				->field('id,name,template,canvas_width,canvas_height,thumbnail_width,thumbnail_url,background_url,logo_src,checked,status')
				->order('weigh desc')
				->select();
			$this->success('返回成功', $list);	
		}
		$this->error(__('非正常访问'));
	}
	
	/**
	 * 关于系统
	 *
	 * @ApiSummary  (flbooth 关于系统)
	 * @ApiMethod   (GET)
	 *
	 */
	public function about()
	{
		$config = get_addon_config('flbooth');
		$this->success('返回成功', [
			'name' => $config['ini']['name'],
			'logo' => $config['ini']['logo'],
			'copyright' => $config['ini']['copyright']
		]);	
	}
	
	/**
	 * 获取上传配置
	 *
	 * @ApiSummary  (flbooth 上传配置)
	 * @ApiMethod   (GET)
	 *
	 */
	public function uploadData()
	{
		$config = get_addon_config('flbooth');
		//配置信息
		$upload = Config::get('upload');
		//如果非服务端中转模式需要修改为中转
		if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
		    //临时修改上传模式为服务端中转
		    set_addon_config($upload['storage'], ["uploadmode" => "server"], false);
		    $upload = \app\common\model\Config::upload();
		    // 上传信息配置后
		    Hook::listen("upload_config_init", $upload);
		    $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
		}
	    // 1.0.8升级
		if($upload['storage'] == 'local'){
			$upload['uploadurl'] = url('/api/common/upload', '', false, true);
		}
		$this->success('返回成功', $upload);
	}
	
	/**
	 * 生成后缀图标
	 */
	public function thumbnail($text = '暂无缩略图', $size = '11')
	{
		header('Content-type: image/svg+xml');
	    $icon = <<<EOT
		<svg style="background-color: #E1F5FF;" viewBox="0 0 200 200" version="1.1" id="booth_com" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve">
			<path fill="#313FA0" d="M157.5,115.9v-4.7l-28.8-28.8c-3.2-3.2-8.4-3.2-11.5,0L76.5,123h73.8C154.3,123,157.5,119.8,157.5,115.9z"/>
			<path fill="#8C9EFF" d="M50.4,123H136L86.3,73.4c-3.2-3.2-8.4-3.2-11.5,0l-31.5,31.3v11.2C43.2,119.8,46.4,123,50.4,123z"/>
			<path fill="#FFD600" d="M117.8,58.5c0,4.5,3.7,8.2,8.2,8.2s8.2-3.7,8.2-8.2s-3.7-8.2-8.2-8.2S117.8,54,117.8,58.5z"/>
			<text fill="#8C9EFF" font-size="{$size}" x="100" y="150" text-anchor="middle">{$text}</text>
		</svg>
EOT;
	    echo $icon;
		exit;
	}
}
