<?php

/*
 * Kvin 整页注册扩展 + 后台 FluxBB 样式
 *
 * 1) /register 和 /login 整页 FluxBB 风格路由 (复用 Flarum 原生注册 API)
 * 2) 后台管理面板注入 admin.less —— Flarum Custom Styles 只进 forum 不进 admin,
 *    要改后台样式只能走 Extend\Frontend('admin')->css()
 */

use Flarum\Extend;
use Kvin\RegisterPage\LoginController;
use Kvin\RegisterPage\RegisterController;

return [
    (new Extend\Routes('forum'))
        ->get('/register', 'kvin.register', RegisterController::class)
        ->get('/login', 'kvin.login', LoginController::class),

    (new Extend\Frontend('admin'))
        ->css(__DIR__ . '/less/admin.less'),
];
