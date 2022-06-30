<?php

namespace app\common\facade;

use app\common\library\token\Driver;
use think\Facade;

/**
 * Token 门面类
 * @see Driver
 * @method Driver get(string $token) static 获取 token 的数据
 * @method Driver set(string $token, string $type, int $user_id, int $expire = 0) static 设置 token
 * @method Driver check(string $token, string $type, int $user_id) static 检查token是否有效
 * @method Driver delete(string $token) static 删除一个token
 * @method Driver clear(string $type, int $user_id) static 清理一个用户的所有token
 */
class Token extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app\common\library\Token';
    }
}