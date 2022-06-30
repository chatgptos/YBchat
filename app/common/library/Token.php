<?php

namespace app\common\library;

use think\facade\Config;
use think\helper\Arr;
use think\helper\Str;
use InvalidArgumentException;

/**
 * Token 管理类
 */
class Token
{
    /**
     * @var Token 实例
     */
    public $instance = [];

    /**
     * @var object token驱动类句柄
     */
    public $handler;

    /**
     * 驱动类命名空间
     */
    protected $namespace = '\\app\\common\\library\\token\\driver\\';

    /**
     * 获取驱动句柄
     * @param string|null $name
     * @return mixed|object
     */
    public function getDriver(string $name = null)
    {
        if (!is_null($this->handler)) {
            return $this->handler;
        }
        $name = $name ?: $this->getDefaultDriver();

        if (is_null($name)) {
            throw new InvalidArgumentException(sprintf(
                'Unable to resolve NULL driver for [%s].',
                static::class
            ));
        }

        return $this->createDriver($name);
    }

    /**
     * 创建驱动句柄
     * @param string $name
     * @return mixed
     */
    protected function createDriver(string $name)
    {
        $type = (string)$this->resolveType($name);

        $method = 'create' . Str::studly($type) . 'Driver';

        $params = $this->resolveParams($name);

        if (method_exists($this, $method)) {
            return $this->$method(...$params);
        }

        $class = $this->resolveClass($type);

        if (isset($this->instance[$type])) {
            return $this->instance[$type];
        }

        return new $class(...$params);
    }

    /**
     * 默认驱动
     * @return string
     */
    protected function getDefaultDriver()
    {
        return $this->getConfig('default');
    }

    /**
     * 获取驱动配置
     * @param string|null $name
     * @param null        $default
     * @return mixed
     */
    protected function getConfig(string $name = null, $default = null)
    {
        if (!is_null($name)) {
            return Config::get('fladmin.token.' . $name, $default);
        }

        return Config::get('fladmin.token');
    }

    /**
     * 获取驱动配置参数
     * @param $name
     * @return array
     */
    protected function resolveParams($name): array
    {
        $config = $this->getStoreConfig($name);
        return [$config];
    }

    /**
     * 获取驱动类
     * @param string $type
     * @return string
     */
    protected function resolveClass(string $type): string
    {
        if ($this->namespace || false !== strpos($type, '\\')) {
            $class = false !== strpos($type, '\\') ? $type : $this->namespace . Str::studly($type);

            if (class_exists($class)) {
                return $class;
            }
        }

        throw new InvalidArgumentException("Driver [$type] not supported.");
    }

    /**
     * 获取驱动配置
     * @param string $store
     * @param string $name
     * @param null   $default
     * @return array
     */
    protected function getStoreConfig(string $store, string $name = null, $default = null)
    {
        if ($config = $this->getConfig("stores.{$store}")) {
            return Arr::get($config, $name, $default);
        }

        throw new \InvalidArgumentException("Store [$store] not found.");
    }

    /**
     * 获取驱动类型
     * @param string $name
     * @return array
     */
    protected function resolveType(string $name)
    {
        return $this->getStoreConfig($name, 'type', 'Mysql');
    }

    /**
     * 设置token
     * @param string   $token
     * @param string   $type
     * @param int      $user_id
     * @param int|null $expire
     * @return bool
     */
    public function set(string $token, string $type, int $user_id, int $expire = null): bool
    {
        return $this->getDriver()->set($token, $type, $user_id, $expire);
    }

    /**
     * 获取token
     * @param string $token
     * @return array
     */
    public function get(string $token): array
    {
        return $this->getDriver()->get($token);
    }

    /**
     * 检查token
     * @param string $token
     * @param string $type
     * @param int    $user_id
     * @return bool
     */
    public function check(string $token, string $type, int $user_id): bool
    {
        return $this->getDriver()->check($token, $type, $user_id);
    }

    /**
     * 删除token
     * @param string $token
     * @return bool
     */
    public function delete(string $token): bool
    {
        return $this->getDriver()->delete($token);
    }

    /**
     * 清理指定用户token
     * @param string $type
     * @param int    $user_id
     * @return bool
     */
    public function clear(string $type, int $user_id): bool
    {
        return $this->getDriver()->clear($type, $user_id);
    }
}