<?php

namespace app\api\validate;

use think\Validate;

class User extends Validate
{
    protected $failException = true;

    protected $rule = [
        'username'  => 'require|regex:^[a-zA-Z][a-zA-Z0-9_]{2,15}$|unique:user',
        'email'     => 'require|email|unique:user',
        'mobile'    => 'require|mobile|unique:user',
        'password'  => 'require|regex:^[a-zA-Z0-9_]{6,32}$',
        'captcha'   => 'require',
        'captchaId' => 'require',
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'login'    => ['password', 'captcha', 'captchaId'],
        'register' => ['email', 'username', 'password', 'mobile', 'captcha', 'captchaId'],
    ];

    public function __construct()
    {
        $this->field   = [
            'username'  => __('username'),
            'email'     => __('email'),
            'mobile'    => __('mobile'),
            'password'  => __('password'),
            'captcha'   => __('captcha'),
            'captchaId' => __('captchaId'),
        ];
        $this->message = array_merge($this->message, [
            'username.regex' => __('Please input correct username'),
            'password.regex' => __('Please input correct password')
        ]);
        parent::__construct();
    }
}