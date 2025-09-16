<?php

namespace dji;

use ba\Alists;
use think\facade\Db;

class Log
{
    private $endpoint = 'oss-cn-chengdu.aliyuncs.com';

    /**
     * 获取可上传日志
     * @return void
     */
    public function getLog()
    {
        $data = [];
        $data['bid'] = uuid();
        $data['tid'] = uuid();
        $data['timestamp'] = round(microtime(true) * 1000);
        $data['method'] = 'fileupload_list';
        $data['data'] = [
            'module_list' => ['0', '3'],
        ];
        $res = publish($data);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 日志入库
     * @return void
     */
    public function saveLog($param)
    {
        if (isset($param['data']['files']) && count($param['data']['files']) > 0) {
            foreach($param['data']['files'] as $key => $val){
                foreach($val['list'] as $listkey => $listvalue){
                    print_r($listvalue);
                    $row = [];
                    $row['boot_index'] = $listvalue['boot_index'];
                    $row['end_time'] = $listvalue['end_time'];
                    $row['module'] = $val['module'];
                    $row['size'] = $listvalue['size'];
                    $row['start_time'] = $listvalue['start_time'];
                    $row['sn'] = $val['device_sn'];
                    Db::name('djilog')->insert($row);
                }
            }
        }
        return true;
    }

    /**
     * 发起日志上传
     * @return void
     */
    public function uploadLog($sn) 
    {
        $alists = new Alists('djiapi');
        $sts = $alists->sts();
        $type = [0,3];
        $data = [];
        $data['topic'] = 'thing/product/'.$sn.'/services';
        $data['bid'] = uuid();
        $data['tid'] = uuid();
        $data['timestamp'] = round(microtime(true) * 1000);
        $data['method'] = 'fileupload_start';
        $data['data'] = [];
        $data['data']['bucket'] = 'djicloudapis';
        $data['data']['credentials'] = $sts;
        $data['data']['endpoint'] = 'https://oss-cn-chengdu.aliyuncs.com';
        $data['data']['provider'] = 'ali';
        $data['data']['region'] = 'cd';
        foreach($type as $key => $value){
            $list = Db::name('djilog')->field('boot_index')->where('module',$value)->limit(1)->order('id','desc')->select();
            $data['data']['params']['files'][$key] = [
                    'list' => $list,
                    'module' => (string)$value,
                    'object_key' => 'log/'.$value.'/'.time().'.log'
            ];
        }
        // print_r(json_encode($data));
        publish($data);
    }
}
