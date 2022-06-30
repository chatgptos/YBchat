<?php
namespace app\api\controller\flbooth;

use app\common\controller\Api;
use fast\Date;

use think\Db;
use think\Exception;
use think\exception\PDOException;

/**
 * flbooth签到接口
 */
class Signin extends Api
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    
   
	/**
	 * 获取签到
	 *
	 * @ApiSummary  (flbooth 获取签到)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $date 日期 2020-06-05
	 */
	public function getSignin()
	{
	    $config = get_addon_config('signin');
		if(!$config){
			$this->error('签到服务不存在！请安装官方发布签到服务');
		}
	    $signdata = $config['signinscore'];
	    $date = $this->request->request('date', date("Y-m-d"), "trim");
	    $time = strtotime($date);
	    $lastdata = \addons\signin\model\Signin::where('user_id', $this->auth->id)->order('created', 'desc')->find();
	    $successions = $lastdata && $lastdata['created'] > Date::unixtime('day', -1) ? $lastdata['successions'] : 0;
	    $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)->whereTime('created', 'today')->find();
	    $list = \addons\signin\model\Signin::where('user_id', $this->auth->id)
	        ->field('id,created')
	        ->whereTime('created', 'between', [date("Y-m-1", $time), date("Y-m-1", strtotime("+1 month", $time))])
	        ->select();
			
		// 用户积分
		$data['user_score'] = $this->auth->score;
		// 已签到日期
		$data['list'] = $list;
		// 今日
		$data['date'] = $date;
		// 补签时消耗的积分
		$data['fillupscore'] = $config['fillupscore'];
		// 是否可以补签
		$data['isfillup'] = $config['isfillup'];
		// 你当前已经连续签到
		$data['successions'] = $successions; 
	    $successions++;
	    $score = isset($signdata['s' . $successions]) ? $signdata['s' . $successions] : $signdata['sn'];
		// 是否签到
		$data['signin'] = $signin;
		// 可获得积分
		$data['score'] = $score;
		// 连续第几天
		$data['signinscore'] = $config['signinscore'];
		$this->success('OK', $data);
	}
	
	
	/**
	 * 立即签到
	 *
	 * @ApiSummary  (flbooth 获取签到)
	 * @ApiMethod   (POST)
	 * 
	 */
	public function dosign()
	{
	    if ($this->request->isPost()) {
	        $config = get_addon_config('signin');
	        $signdata = $config['signinscore'];
	
	        $lastdata = \addons\signin\model\Signin::where('user_id', $this->auth->id)->order('created', 'desc')->find();
	        $successions = $lastdata && $lastdata['created'] > Date::unixtime('day', -1) ? $lastdata['successions'] : 0;
	        $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)->whereTime('created', 'today')->find();
	        if ($signin) {
	            $this->error('今天已签到,请明天再来!');
	        } else {
	            $successions++;
	            $score = isset($signdata['s' . $successions]) ? $signdata['s' . $successions] : $signdata['sn'];
	            Db::startTrans();
	            try {
	                \addons\signin\model\Signin::create(['user_id' => $this->auth->id, 'successions' => $successions, 'created' => time()]);
	                \app\common\model\User::score($score, $this->auth->id, "连续签到{$successions}天");
	                Db::commit();
	            } catch (Exception $e) {
	                Db::rollback();
	                $this->error('签到失败,请稍后重试');
	            }
	            $this->success('OK','签到成功!连续签到' . $successions . '天!获得' . $score . '积分');
	        }
	    }
	    $this->error("请求错误");
	}
	
	/**
	 * 签到补签
	 *
	 * @ApiSummary  (flbooth 签到补签)
	 * @ApiMethod   (GET)
	 * 
	 * @param string $date 日期 2020-06-05
	 */
	public function fillup()
	{
	    $date = $this->request->request('date');
	    $time = strtotime($date);
	    $config = get_addon_config('signin');
	    if (!$config['isfillup']) {
	        $this->error('暂未开启签到补签');
	    }
	    if ($time > time()) {
	        $this->error('无法补签未来的日期');
	    }
	    if ($config['fillupscore'] > $this->auth->score) {
	        $this->error('你当前积分不足');
	    }
	    $days = Date::span(time(), $time, 'days');
	    if ($config['fillupdays'] < $days) {
	        $this->error("只允许补签{$config['fillupdays']}天的签到");
	    }
	    $count = \addons\signin\model\Signin::where('user_id', $this->auth->id)
	        ->where('type', 'fillup')
	        ->whereTime('created', 'between', [Date::unixtime('month'), Date::unixtime('month', 0, 'end')])
	        ->count();
	    if ($config['fillupnumsinmonth'] <= $count) {
	        $this->error("每月只允许补签{$config['fillupnumsinmonth']}次");
	    }
	    Db::name('signin')->whereTime('created', 'd')->select();
	    $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)
	        ->where('type', 'fillup')
	        ->whereTime('created', 'between', [$date, date("Y-m-d 23:59:59", $time)])
	        ->count();
	    if ($signin) {
	        $this->error("该日期无需补签到");
	    }
	    $successions = 1;
	    $prev = $signin = \addons\signin\model\Signin::where('user_id', $this->auth->id)
	        ->whereTime('created', 'between', [date("Y-m-d", strtotime("-1 day", $time)), date("Y-m-d 23:59:59", strtotime("-1 day", $time))])
	        ->find();
	    if ($prev) {
	        $successions = $prev['successions'] + 1;
	    }
	    Db::startTrans();
	    try {
	        \app\common\model\User::score(-$config['fillupscore'], $this->auth->id, '签到补签');
	        //寻找日期之后的
	        $nextList = \addons\signin\model\Signin::where('user_id', $this->auth->id)
	            ->where('created', '>=', strtotime("+1 day", $time))
	            ->order('created', 'asc')
	            ->select();
	        foreach ($nextList as $index => $item) {
	            //如果是阶段数据，则中止
	            if ($index > 0 && $item->successions == 1) {
	                break;
	            }
	            $day = $index + 1;
	            if (date("Y-m-d", $item->created) == date("Y-m-d", strtotime("+{$day} day", $time))) {
	                $item->successions = $successions + $day;
	                $item->save();
	            }
	        }
	        \addons\signin\model\Signin::create(['user_id' => $this->auth->id, 'type' => 'fillup', 'successions' => $successions, 'created' => $time + 43200]);
	        Db::commit();
	    } catch (PDOException $e) {
	        Db::rollback();
	        $this->error('补签失败,请稍后重试');
	    } catch (Exception $e) {
	        Db::rollback();
	        $this->error('补签失败,请稍后重试');
	    }
	    $this->success('OK','补签成功');
	}
	
	/**
	 * 排行榜
	 *
	 * @ApiSummary  (flbooth 签到补签)
	 * @ApiMethod   (GET)
	 * 
	 */
	public function rank()
	{
		$config = get_addon_config('signin');
		if(!$config){
			$this->error('签到服务不存在！请安装官方发布签到服务');
		}
	    $data = \addons\signin\model\Signin::with(["user"])
	        // ->where("created", ">", \fast\Date::unixtime('day', -1))
	        ->field("user_id,MAX(successions) AS days")
	        ->group("user_id")
	        ->order("days", "desc")
	        ->limit(10)
	        ->select();
	    foreach ($data as $index => $datum) {
	        $datum->getRelation('user')->visible(['id', 'username', 'nickname', 'avatar', 'score']);
	    }
	    $this->success("OK", ['ranklist' => collection($data)->toArray()]);
	}
	
}
