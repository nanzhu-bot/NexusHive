<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\facade\Cache;
use think\facade\Db;

class Dashboard extends Backend
{
    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): void
    {
        $this->success('', [
            'remark' => get_route_remark()
        ]);
    }

    public function osd(): void
    {
        //查询设备数据
        $eq_list = Db::name('equipment')->select();
        //循环获取所有历史数据并叠加
        $total_flight_distance = 0;
        $total_flight_time = 0;
        $total_flight_sorties = 0;
        $total_airline = Db::name('airline')->count();
        foreach($eq_list as $key => $eq){
            $now_sn_flight_distance = Cache::get($eq['sn'].'_total_flight_distance',0);
            $total_flight_distance += $now_sn_flight_distance;
            $now_sn_flight_time = Cache::get($eq['sn'].'_total_flight_time',0);
            $total_flight_time += $now_sn_flight_time;
            $now_sn_flight_sorties = Cache::get($eq['sn'].'_total_flight_sorties',0);
            $total_flight_sorties += $now_sn_flight_sorties;
        }
        //历史数据统计
        $this->success('返回成功', [
            'total_flight_distance' => number_format($total_flight_distance / 1000,2) . 'km',
            'total_flight_time' => number_format($total_flight_time / 60 / 60,2) . 'h',
            'total_flight_sorties' => $total_flight_sorties,
            'total_airline' => $total_airline,
        ]);
    }
}