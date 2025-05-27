<?php
/**
 * Anon配置
 */
if (!defined('ANON_ALLOWED_ACCESS')) exit;

class Anon
{
    public static function run()
    {
        $Root = __DIR__ . '/';
        $Server  = __DIR__ . '/Server/';
        $Modules = __DIR__ . '/Modules/';
        
        // 先加载配置类
        require_once $Modules . 'Config.php';
        
        // 检查是否已安装
        if (!Anon_Config::isInstalled()) {
            header('Location: /anon/install');
            exit;
        }
        
        // 已安装则加载其他必要文件
        require_once $Server  . 'Database.php';
        require_once $Modules . 'Check.php';
        require_once $Root . 'Setup.php';
        require_once $Modules . 'Router.php';
    }
}

// 启动应用
Anon::run();