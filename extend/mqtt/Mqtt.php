<?php

namespace mqtt;

use app\admin\model\Equipment;
use dji\Airline;
use dji\Main;
use think\facade\Db;
use think\worker\Server;
use Workerman\Lib\Timer;
use Workerman\Worker;

// mqtt类继承think\worker\Server
class Mqtt extends Server
{
    private $connection = null;
    private $main = null;
    private $airline = null;
    
    /**
     * 订阅设备相关的所有主题
     * @param object $mqtt MQTT客户端实例
     * @param string $sn 设备序列号
     */
    private function subscribeEquipmentTopics($mqtt, $sn)
    {
        $topics = [
            "thing/product/{$sn}/events",
            "sys/product/{$sn}/status", 
            "thing/product/{$sn}/osd",
            "thing/product/{$sn}/requests",
            "thing/product/{$sn}/services_reply",
            "thing/product/{$sn}/flighttask_progress"
        ];
        
        foreach ($topics as $topic) {
            $mqtt->subscribe($topic);
        }
    }
    
    public function onWorkerStart($worker)
    {
        $this->main = new Main();
        $this->airline = new Airline();
        $options = [
            'keepalive' => 60,
            'client_id' => "PUBLISH0001",
            'clean_session' => true,
            'reconnect_period' => 10,
            'username' => "wangxudong",
            "password" => "Admin@1234567890"
        ];
        $mqtt = new \Workerman\Mqtt\Client('mqtt://121.5.46.95:1883', $options); // mqtt://mqt.test.com:1883 这个域名请填写自己Broker的 域名
        //获取设备
        $list = Equipment::select();
        $this->connection = $mqtt;
        // 订阅mqtt主题消息
        $mqtt->onConnect = function ($mqtt) use ($list) {
            // 订阅系统设备添加主题
            $mqtt->subscribe('system/equipment/set/equipment_add');
            
            // 批量订阅设备相关主题
            foreach ($list as $equipment) {
                $this->subscribeEquipmentTopics($mqtt, $equipment['sn']);
            }
        };
        $mqtt->onMessage = function ($topic, $content,$mqtt) {
            $topicArr = explode('/', $topic);
            if(count($topicArr) == 4){
                $data = json_decode($content, true);
                $data['sn'] = $topicArr[2];
                switch($topicArr[3]){
                    case 'equipment_add':
                        //新设备入场 增加对应订阅
                        $this->subscribeEquipmentTopics($mqtt, $data['new_sn']);
                        break;
                    case 'events':
                        $this->main->responseEvents($data);
                        break;
                    case 'requests':
                        $this->main->responseRequest($data);
                        break;
                    case 'services_reply':
                        $this->main->responseServicesReply($data);
                        break;
                    case 'osd':
                        //同步订阅子设备
                        if(isset($data['data']['sub_device']['device_sn'])){
                            $this->connection->subscribe('thing/product/' . $data['data']['sub_device']['device_sn'] . '/osd');
                        }
                        $this->main->responseOsd($data);
                        break;
                    case 'status':
                        $this->main->responseStatus($data);
                        break;
                }
            }
        };
        $mqtt->connect();
        $inner_text_worker = new Worker('text://0.0.0.0:1884'); //内部调用端口
        $inner_text_worker->onMessage = function ($connection, $message) {
            $arr = json_decode($message, true);
            $topic = $arr['topic'];
            unset($arr['topic']);
            if (isset($this->connection)) {
                $this->connection->publish($topic, json_encode($arr));
                $connection->send('ok');
            } else {
                $connection->send('error: MQTT connection not initialized');
            }
        };
        $inner_text_worker->listen();

        Timer::add(5, function () {
            $tast_list = Db::name('flighttask')->where('status','sent')->where('execute_time','<=',time())->select();
            print_r(count($tast_list));
            if ($tast_list) {
                if (count($tast_list) > 0) {
                    $this->airline->flighttaskReady($tast_list);
                }
            }
        });
    }
}
