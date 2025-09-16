<?php

use ba\Filesystem;
use GuzzleHttp\Client;

if (!function_exists('get_controller_list')) {
    function get_controller_list($app = 'admin'): array
    {
        $controllerDir = root_path() . 'app' . DIRECTORY_SEPARATOR . $app . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR;
        return Filesystem::getDirFiles($controllerDir);
    }
}

if (!function_exists('get_ba_client')) {
    /**
     * 获取一个请求 BuildAdmin 开源社区的 Client
     * @throws Throwable
     */
    function get_ba_client(): Client
    {
        return new Client([
            'base_uri'        => config('buildadmin.api_url'),
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'BuildAdminClient',
            ]
        ]);
    }
}