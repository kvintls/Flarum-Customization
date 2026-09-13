<?php

/*
 * Kvin 整页注册扩展
 *
 * 加一个真实的 /register 页面路由：服务端直接渲染整页 FluxBB 风格注册页。
 * 表单通过 fetch 调用 Flarum 自带的 POST /api/users 完成注册（复用原生校验/发信逻辑），
 * 不重写后端。CSRF token 从当前会话取出，内联进页面。
 */

use Flarum\Extend;
use Kvin\RegisterPage\LoginController;
use Kvin\RegisterPage\RegisterController;

return [
    (new Extend\Routes('forum'))
        ->get('/register', 'kvin.register', RegisterController::class)
        ->get('/login', 'kvin.login', LoginController::class),
];
