<?php

namespace app;

/**
 * 应用请求对象类
 */
class Request extends \think\Request
{
    /**
     * 全局过滤规则
     * app/common.php 的 filter 函数
     */
    protected $filter = 'filter';

    public function __construct()
    {
        parent::__construct();

        // 从配置文件读取代理服务器ip，并设置给 \think\Request
        $proxyServerIp = config('buildadmin.proxy_server_ip');
        if (is_array($proxyServerIp) && $proxyServerIp) {
            $this->proxyServerIp = $proxyServerIp;
        }
    }
}
