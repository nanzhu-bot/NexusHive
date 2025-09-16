<?php

namespace dji;

use app\admin\model\Flightosd;

class Osd
{
    public function osdSave($param)
    {
        $osd_model = new Flightosd();
        $data = [
            'sn' => $param['sn'],
            'dock_sn' => $param['gateway'],
            'latitude' => $param['data']['latitude'],
            'longitude' => $param['data']['longitude'],
        ];
        //同时缓存里程及航行时间
        if(isset($param['data']['total_flight_distance'])) cache($param['gateway'].'_total_flight_distance', $param['data']['total_flight_distance']); //里程
        if(isset($param['data']['total_flight_time'])) cache($param['gateway'].'_total_flight_time', $param['data']['total_flight_time']);  //航行时间
        if(isset($param['data']['mode_code'])) cache($param['gateway'].'_mode_code', $param['data']['mode_code']); //飞行器状态
        if(isset($param['data']['mode_code_reason'])) cache($param['gateway'].'_mode_code_reason', $param['data']['mode_code_reason']); //飞行器状态原因
        if(isset($param['data']['vertical_speed'])) cache($param['gateway'].'_vertical_speed', $param['data']['vertical_speed']); //垂直速度 
        if(isset($param['data']['horizontal_speed'])) cache($param['gateway'].'_horizontal_speed', $param['data']['horizontal_speed']); //水平速度 
        if(isset($param['data']['total_flight_sorties'])) cache($param['gateway'].'_total_flight_sorties', $param['data']['total_flight_sorties']); //总架次
        // $pub = [];
        // $pub['topic'] = 'aaa/bbb';
        // $pub['data'] = [
        //     'total_flight_distance' => $param['data']['total_flight_distance'],
        //     'total_flight_time' => $param['data']['total_flight_time'],
        //     'mode_code' => $param['data']['mode_code'],
        //     'mode_code_reason' => isset($param['data']['mode_code_reason']) ? $param['data']['mode_code_reason'] : 0,
        //     'vertical_speed' => $param['data']['vertical_speed'],
        //     'horizontal_speed' => $param['data']['horizontal_speed'],
        //     'total_flight_sorties' => $param['data']['total_flight_sorties'],
        // ];
        // publish($pub);
        $osd_model->save($data);
    }

    public function statusReady($param, $other)
    {
        $data = [];
        $data['bid'] = $param['bid'];
        $data['tid'] = $param['tid'];
        $data['timestamp'] = round(microtime(true) * 1000);
        $data['method'] = $param['method'];
        $data['topic'] = 'sys/product/' . $param['sn'] . '/status_reply';
        $data['data'] = $other;
        publish($data);
    }
}
