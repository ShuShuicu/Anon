<?php
if (!defined('ANON_ALLOWED_ACCESS')) exit;
/**
 * 注册路由
 * @param string $path 路由路径
 * @param callable $handler 处理函数
 * @return void
 */
$RouterDir = __DIR__ . '/../app/Router/';
Anon_Config::addRoute('', function () use ($RouterDir) {
    require_once $RouterDir . 'Home.php';
});

Anon_Config::addErrorHandler(404, function() {
    http_response_code(404);
    echo '404 - 页面未找到';
});

/**
 * Anon默认路由
 */
// 安装程序
Anon_Config::addRoute('anon/install', function () use ($RouterDir) {
    require_once __DIR__ . '/Modules/Install/Install.php';
});
// 退出登录
Anon_Config::addRoute('anon/logout', function () use ($RouterDir) {
    Anon_Check::logout();
});