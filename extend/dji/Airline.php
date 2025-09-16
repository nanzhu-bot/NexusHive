<?php

namespace dji;

use app\admin\model\Airline as ModelAirline;
use app\admin\model\Equipment;
use app\admin\model\Flightrecord;
use app\admin\model\Flighttask;
use ba\Alists;
use think\facade\Cache;
use think\facade\Db;

class Airline
{
    public $endpoint = '';
    /**
     * 航线任务下发接口 - 根据DJI官方API优化
     * @param array $param 任务参数
     * @return array 返回结果
     */
    public function pushTask($param)
    {
        // print_r($param);
        try {
            // 1. 参数验证
            $validation = $this->validatePushTaskParams($param);
            if (!$validation['status']) {
                return $validation;
            }

            // 2. 获取航线和设备信息
            // $wayline = ModelAirline::find($param['airline_id']);
            // if (!$wayline) {
            //     return ['status' => false, 'code' => 1001, 'msg' => '下发失败,未查询到对应航线！'];
            // }
            $equipment = Equipment::find($param['equipment_id']);
            if (!$equipment) {
                return ['status' => false, 'code' => 1002, 'msg' => '下发失败,未查询到对应设备！'];
            }

            // 3. 构建MQTT消息
            $mqttData = $this->buildFlightTaskPrepareMessage($param, $equipment);
            // 4. 发布消息
            $result = publish($mqttData);
            
            if ($result) {
                // 5. 记录任务状态
                // $this->recordTaskStatus($param, 'sent');
                return ['status' => true, 'code' => 0, 'msg' => '任务下发成功', 'data' => ['flight_id' => $param['bid']]];
            } else {
                return ['status' => false, 'code' => 1003, 'msg' => '任务下发失败,MQTT发布失败'];
            }

        } catch (\Exception $e) {
            return ['status' => false, 'code' => 1004, 'msg' => '任务下发异常: ' . $e->getMessage()];
        }
    }

    /**
     * 验证pushTask参数
     * @param array $param
     * @return array
     */
    private function validatePushTaskParams($param)
    {
        // 必需参数检查
        $requiredFields = ['bid', 'tid', 'airline_id', 'equipment_id'];
        foreach ($requiredFields as $field) {
            if (!isset($param[$field]) || empty($param[$field])) {
                return ['status' => false, 'code' => 1000, 'msg' => "缺少必需参数: {$field}"];
            }
        }

        // 任务类型验证
        if (!in_array($param['task_type'], [0, 1, 2])) {
            return ['status' => false, 'code' => 1005, 'msg' => '任务类型无效,支持: 0=立即任务,1=定时任务,2=条件任务'];
        }

        // 定时任务和立即任务需要执行时间
        if (in_array($param['task_type'], [0, 1]) && empty($param['execute_time'])) {
            return ['status' => false, 'code' => 1006, 'msg' => '立即任务和定时任务需要指定执行时间'];
        }

        // 条件任务需要就绪条件
        if ($param['task_type'] == 2 && empty($param['ready_conditions'])) {
            return ['status' => false, 'code' => 1007, 'msg' => '条件任务需要指定就绪条件'];
        }

        return ['status' => true];
    }

    /**
     * 构建flighttask_prepare消息
     * @param array $param
     * @param object $wayline
     * @param object $equipment
     * @return array
     */
    private function buildFlightTaskPrepareMessage($param, $equipment)
    {
        $topic = 'thing/product/' . $equipment['sn'] . '/services';
        
        // 基础消息结构
        $data = [
            'bid' => $param['bid'],
            'tid' => $param['tid'],
            'timestamp' => round(microtime(true) * 1000),
            'method' => 'flighttask_prepare',
            'topic' => $topic,
            'data' => []
        ];

        // 任务数据
        $taskData = [
            'flight_id' => $param['bid'],
            'task_type' => (int)$param['task_type'],
            'file' => [
                'url' => $this->endpoint . $param['file_url'],
                'fingerprint' => $this->calculateFileMD5($this->endpoint . $param['file_url'])
            ]
        ];

        // 执行时间设置
        if (isset($param['execute_time'])) {
            if ($param['task_type'] == 0) {
                // 立即任务
                $taskData['execute_time'] = round(microtime(true) * 1000);
            } elseif ($param['task_type'] == 1) {
                // 定时任务
                $taskData['execute_time'] = is_numeric($param['execute_time']) 
                    ? $param['execute_time'] * 1000 
                    : strtotime($param['execute_time']) * 1000;
            }
        }

        // 返航设置
        $taskData['rth_altitude'] = isset($param['rth_altitude']) ? (int)$param['rth_altitude'] : 100;
        $taskData['rth_mode'] = isset($param['rth_mode']) ? (int)$param['rth_mode'] : 1;
        
        // 失控动作设置
        $taskData['out_of_control_action'] = isset($param['out_of_control_action']) ? (int)$param['out_of_control_action'] : 0;
        $taskData['exit_wayline_when_rc_lost'] = isset($param['exit_wayline_when_rc_lost']) ? (int)$param['exit_wayline_when_rc_lost'] : 0;
        
        // 航线精度类型
        $taskData['wayline_precision_type'] = isset($param['wayline_precision_type']) ? (int)$param['wayline_precision_type'] : 1;

        // 模拟任务设置
        // if (isset($param['simulate_mission']) && $param['simulate_mission']) {
            $taskData['simulate_mission'] = [
                'is_enable' => 1,
                'latitude' => isset($param['simulate_latitude']) ? (float)$param['simulate_latitude'] : 30.46822907,
                'longitude' => isset($param['simulate_longitude']) ? (float)$param['simulate_longitude'] : 105.562635819
            ];
        // }

        // 就绪条件(条件任务)
        if ($param['task_type'] == 2 && isset($param['ready_conditions'])) {
            $taskData['ready_conditions'] = [
                'battery_capacity' => isset($param['ready_conditions']['battery_capacity']) ? (int)$param['ready_conditions']['battery_capacity'] : 90,
                'begin_time' => isset($param['ready_conditions']['begin_time']) ? $param['ready_conditions']['begin_time'] : round(microtime(true) * 1000),
                'end_time' => isset($param['ready_conditions']['end_time']) ? $param['ready_conditions']['end_time'] : round(microtime(true) * 1000) + 86400000 // 默认24小时后
            ];
        }

        // 执行条件
        if (isset($param['executable_conditions'])) {
            $taskData['executable_conditions'] = [
                'storage_capacity' => isset($param['executable_conditions']['storage_capacity']) ? (int)$param['executable_conditions']['storage_capacity'] : 1000
            ];
        }

        // 断点续飞
        if (isset($param['break_point'])) {
            $taskData['break_point'] = [
                'index' => (int)$param['break_point']['index'],
                'state' => (int)$param['break_point']['state'],
                'progress' => (float)$param['break_point']['progress'],
                'wayline_id' => (int)$param['break_point']['wayline_id']
            ];
        }

        // 飞行安全预检查
        if (isset($param['flight_safety_advance_check'])) {
            $taskData['flight_safety_advance_check'] = (bool)$param['flight_safety_advance_check'];
        }

        $data['data'] = $taskData;
        return $data;
    }

    /**
     * 计算文件MD5值
     * @param string $url
     * @return string
     */
    private function calculateFileMD5($url)
    {
        try {
            // 如果是本地文件路径
            if (file_exists($url)) {
                return md5_file($url);
            }
            
            // 如果是远程URL，下载后计算MD5
            $content = file_get_contents($url);
            if ($content !== false) {
                return md5($content);
            }
            
            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 记录任务状态
     * @param array $param
     * @param string $status
     */
    private function recordTaskStatus($param, $status)
    {
        try {
            // 更新或创建任务记录
            Flighttask::updateOrCreate(
                ['bid' => $param['bid']],
                [
                    'equipment_id' => $param['equipment_id'],
                    'airline_id' => $param['airline_id'],
                    'status' => $status,
                    'task_type' => $param['task_type'],
                    'create_time' => time(),
                    'update_time' => time()
                ]
            );
        } catch (\Exception $e) {
            // 记录日志但不影响主流程
            error_log('记录任务状态失败: ' . $e->getMessage());
        }
    }

    public function resourceReady($param)
    {
        if (isset($param['data']['flight_id']) && $param['data']['flight_id']) {
            $task = Flighttask::where('bid', $param['data']['flight_id'])->find();
            $data = [];
            $data['bid'] = $param['bid'];
            $data['tid'] = $param['tid'];
            $data['timestamp'] = round(microtime(true) * 1000);
            $data['method'] = 'flighttask_resource_get';
            $data['topic'] = 'thing/product/' . $param['sn'] . '/requests_reply';
            $data['data'] = [];
            $data['data']['result'] = 0;
            $data['data']['output'] = [];
            $data['data']['output']['file'] = [];
            $data['data']['output']['file']['fingerprint'] = md5_file($this->endpoint . $task['file_url']);
            $data['data']['output']['file']['url'] = $this->endpoint . $task['file_url'];
            $result = publish($data);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function flighttaskReady($param)
    {
        foreach ($param as $key => $value) {
            $row = $value;
            if ($row) {
                $erow = Equipment::find($row['equipment_id']);
                if ($erow) {
                    //组装任务执行参数
                    $data = [];
                    $data['bid'] = $value['bid'];
                    $data['tid'] = uuid();
                    $data['topic'] = 'thing/product/' . $erow['sn'] . '/services';
                    $data['timestamp'] = round(microtime(true) * 1000);
                    $data['method'] = 'flighttask_execute';
                    $data['data'] = [];
                    $data['data']['flight_id'] = $value['bid'];
                    $result = publish($data);
                    Flighttask::where('bid', $value['bid'])->update(['status' => 'in_progress']);
                    if ($result) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function flighttaskReady_Bak($param)
    {
        if (isset($param['data']['flight_ids']) && count($param['data']['flight_ids']) > 0) {
            foreach ($param['data']['flight_ids'] as $key => $value) {
                $row = Flighttask::where('bid', $value)->find();
                if ($row) {
                    $erow = Equipment::find($row['equipment_id']);
                    if ($erow) {
                        //组装任务执行参数
                        $data = [];
                        $data['bid'] = $value;
                        $data['tid'] = uuid();
                        $data['topic'] = 'thing/product/' . $erow['sn'] . '/services';
                        $data['timestamp'] = round(microtime(true) * 1000);
                        $data['method'] = 'flighttask_execute';
                        $data['data'] = [];
                        $data['data']['flight_id'] = $value;
                        $result = publish($data);
                        Flighttask::where('bid', $value)->update(['status' => 1]);
                        if ($result) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    public function flighttaskErrot($param)
    {
        if (isset($param['data']['result']) && $param['data']['result'] > 0) {
            $eq_row = Equipment::where('sn',$param['sn'])->find();
            $row = Flighttask::where('equipment_id', $eq_row['id'])->order('id','desc')->find();
            Flighttask::where('id', $row['id'])->update(['status' => 'failed','error_code' => $param['data']['result']]);
            return true;
        }
    }

    public function flighttask_progress($param)
    {
        if (isset($param['data']['result']) && $param['data']['result'] < 1) {
            $output = $param['data']['output'];
            //记录飞行日志
            $data = [
                'sn' => $param['sn'],
                'flight_id' => $output['ext']['flight_id'],
                'track_id' => $output['ext']['track_id'],
                'current_waypoint_index' => $output['ext']['current_waypoint_index'],
                'media_count' => isset($output['ext']['media_count']) ? $output['ext']['media_count'] : 0,
                'wayline_mission_state' => $output['ext']['wayline_mission_state'],
                'wayline_id' => $output['ext']['wayline_id'],
                'progress_current_step' => $output['progress']['current_step'],
                'progress_percent' => $output['progress']['percent'],
                'create_time' => time(),
                'update_time' => time()
            ];
            Flightrecord::create($data);
            // 同时更新对应任务的进度
            if (isset($output['status'])) {
                $this->updateFlightTaskProgress($output, $param);
            }
            
            // 更新媒体文件总数
            if (isset($output['ext']['media_count']) && $output['ext']['media_count'] > 0) {
                Flighttask::where('bid', $output['ext']['flight_id'])
                    ->update(['media_total' => $output['ext']['media_count']]);
            }
        }
    }

    /**
     * 更新飞行任务进度
     * 
     * @param array $output 输出数据
     * @param array $param 参数数据
     * @return void
     */
    private function updateFlightTaskProgress($output, $param)
    {
        $flightId = $output['ext']['flight_id'];
        $currentStatus = $output['status'];
        $currentWaypointIndex = $output['ext']['current_waypoint_index'];
        
        // 获取缓存中的状态
        $cachedStatus = Cache::get($flightId);
        $cachedWaypointIndex = Cache::get($flightId . 'current_waypoint_index');
        
        // 处理失败状态
        if ($currentStatus === 'failed') {
            $this->handleFailedStatus($flightId, $param['data']['result'], $currentStatus);
        } else {
            // 处理正常状态更新
            $this->handleNormalStatusUpdate(
                $flightId, 
                $currentStatus, 
                $currentWaypointIndex, 
                $cachedStatus, 
                $cachedWaypointIndex
            );
        }
        
        // 更新缓存
        Cache::set($flightId, $currentStatus);
        Cache::set($flightId . 'current_waypoint_index', $currentWaypointIndex);
    }

    /**
     * 处理失败状态
     * 
     * @param string $flightId 飞行任务ID
     * @param int $errorCode 错误代码
     * @param string $status 状态
     * @return void
     */
    private function handleFailedStatus($flightId, $errorCode, $status)
    {
        Flighttask::where('bid', $flightId)->update([
            'error_code' => $errorCode,
            'status' => $status
        ]);
    }

    /**
     * 处理正常状态更新
     * 
     * @param string $flightId 飞行任务ID
     * @param string $currentStatus 当前状态
     * @param int $currentWaypointIndex 当前航点索引
     * @param string $cachedStatus 缓存状态
     * @param int $cachedWaypointIndex 缓存航点索引
     * @return void
     */
    private function handleNormalStatusUpdate($flightId, $currentStatus, $currentWaypointIndex, $cachedStatus, $cachedWaypointIndex)
    {
        $updateData = [];
        $needUpdate = false;
        
        // 状态和航点都发生变化
        if ($cachedStatus !== $currentStatus && $cachedWaypointIndex !== $currentWaypointIndex) {
            if ($currentStatus == 'ok') {
                $updateData['end_time'] = time();
            }
            $updateData['status'] = $currentStatus;
            $updateData['now_point'] = $currentWaypointIndex;
            $needUpdate = true;
        } else {
            // 仅状态发生变化
            if ($cachedStatus !== $currentStatus) {
                $updateData['status'] = $currentStatus;
                if ($currentStatus == 'ok') {
                    $updateData['end_time'] = time();
                }
                $needUpdate = true;
            }
            
            // 仅航点发生变化
            if ($cachedWaypointIndex !== $currentWaypointIndex) {
                $updateData['now_point'] = $currentWaypointIndex;
                $needUpdate = true;
            }
        }
        
        // 执行更新
        if ($needUpdate) {
            Flighttask::where('bid', $flightId)->update($updateData);
        }
    }

    public function storageConfigReady($param)
    {
        echo 'bucket is djicloudapis';
        $alists = new Alists('djicloudapis');
        $sts = $alists->sts();
        $data = [];
        $data['topic'] = 'thing/product/' . $param['sn'] . '/requests_reply';
        $data['bid'] = $param['bid'];
        $data['tid'] = $param['tid'];
        $data['timestamp'] = round(microtime(true) * 1000);
        $data['method'] = 'storage_config_get';
        $data['data'] = [
            'output' => [],
            'result' => 0
        ];
        $data['data']['output']['bucket'] = 'djicloudapis';
        $data['data']['output']['credentials'] = $sts;
        $data['data']['output']['endpoint'] = 'https://oss-cn-chengdu.aliyuncs.com';
        $data['data']['output']['object_key_prefix'] = $param['sn'];
        $data['data']['output']['provider'] = 'ali';
        $data['data']['output']['region'] = 'cd';
        publish($data);
    }

    /**
     * 文件上传回调处理函数 - 将上传文件信息入库
     * @param array $param 回调参数
     * @return array 处理结果
     */
    public function file_upload_callback($param)
    {
        // 参数验证
        if (!isset($param) || !is_array($param) || !isset($param['data']['file'])) {
            return ['status' => false, 'code' => -1, 'msg' => '参数无效或文件信息缺失'];
        }

        try {
            // 提取文件信息
            $fileInfo = $param['data']['file'];
            $extInfo = isset($fileInfo['ext']) ? $fileInfo['ext'] : [];
            $metadata = isset($fileInfo['metadata']) ? $fileInfo['metadata'] : [];
            $shootPosition = isset($metadata['shoot_position']) ? $metadata['shoot_position'] : [];
            
            // 通过文件名后缀判断文件类型：0=图片,1=视频,2=其它
            $fileType = 2; // 默认类型为其它
            $fileName = isset($fileInfo['name']) ? $fileInfo['name'] : '';
            if (!empty($fileName)) {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                // 图片类型后缀
                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp'];
                // 视频类型后缀
                $videoExtensions = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
                
                if (in_array($extension, $imageExtensions)) {
                    $fileType = 0; // 图片
                } elseif (in_array($extension, $videoExtensions)) {
                    $fileType = 1; // 视频
                }
            }
            
            // 组装入库数据
            $data = [
                'type' => $fileType, // 文件类型：0=图片,1=视频,2=其它
            
                'sn' => isset($param['gateway']) ? $param['gateway'] : '', // 使用gateway作为sn
                'name' => isset($fileInfo['name']) ? $fileInfo['name'] : '',
                'object_key' => isset($fileInfo['object_key']) ? $fileInfo['object_key'] : '',
                'path' => isset($fileInfo['path']) ? $fileInfo['path'] : '',
                'flight_id' => isset($extInfo['flight_id']) ? $extInfo['flight_id'] : '',
                'drone_model_key' => isset($extInfo['drone_model_key']) ? $extInfo['drone_model_key'] : '',
                'payload_model_key' => isset($extInfo['payload_model_key']) ? $extInfo['payload_model_key'] : '',
                'is_original' => isset($extInfo['is_original']) && $extInfo['is_original'] ? '1' : '0',
                'gimbal_yaw_degree' => isset($metadata['gimbal_yaw_degree']) ? $metadata['gimbal_yaw_degree'] : null,
                'absolute_altitude' => isset($metadata['absolute_altitude']) ? $metadata['absolute_altitude'] : null,
                'relative_altitude' => isset($metadata['relative_altitude']) ? $metadata['relative_altitude'] : null,
                'c_time' => isset($metadata['created_time']) ? $metadata['created_time'] : null,
                'lat' => isset($shootPosition['lat']) ? $shootPosition['lat'] : null,
                'lng' => isset($shootPosition['lng']) ? $shootPosition['lng'] : null,
                'create_time' => time()
            ];

            // 插入数据库
            $result = Db::table('nz_media')->insert($data);
            
            if ($result) {
                // 更新对应飞行任务的已上传媒体文件数量
                $this->updateFlightTaskMediaCount($data['flight_id']);
                
                return ['status' => true, 'code' => 0, 'msg' => '文件信息入库成功'];
            } else {
                return ['status' => false, 'code' => -2, 'msg' => '文件信息入库失败'];
            }
        } catch (\Exception $e) {
            return ['status' => false, 'code' => -3, 'msg' => '处理异常: ' . $e->getMessage()];

        }
    }

    /**
     * 更新飞行任务的已上传媒体文件数量
     * 
     * @param string $flightId 飞行任务ID
     * @return void                                                                                                                                                                                                               
     */
    private function updateFlightTaskMediaCount($flightId)
    {
        if (empty($flightId)) {
            return;
        }

        try {
            // 统计该飞行任务已上传的媒体文件数量
            $mediaCount = Db::table('nz_media')
                ->where('flight_id', $flightId)
                ->count();

            // 更新飞行任务的media_now字段
            Flighttask::where('bid', $flightId)
                ->update(['media_now' => $mediaCount]);

        } catch (\Exception $e) {
            // 记录错误日志但不影响主流程
            error_log('更新飞行任务媒体文件数量失败: ' . $e->getMessage());
        }
    }
}