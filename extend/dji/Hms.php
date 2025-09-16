<?php

namespace dji;

use think\facade\Db;

class Hms
{
    public function save($param)
    {
        // 检查参数是否有效
        if (empty($param) || !isset($param['data']) || !isset($param['data']['list']) || !is_array($param['data']['list'])) {
            return ['code' => -1, 'msg' => '参数无效'];
        }

        // 获取hms.json文件内容
        // 注意：这里需要根据实际项目结构调整文件路径
        $jsonPath = '/hms.json'; // 假设hms.json位于项目根目录
        
        // 如果找不到hms.json，尝试从URL获取（如果需要）
        if (!file_exists($jsonPath)) {
            // 可以选择从URL获取，这里注释掉
            $url = 'https://terra-1-g.djicdn.com/fee90c2e03e04e8da67ea6f56365fc76/SDK%20%E6%96%87%E6%A1%A3/CloudAPI/hms.json';
            $jsonContent = file_get_contents($url);
            // return ['code' => -2, 'msg' => '无法找到hms.json文件，请检查路径: ' . $jsonPath];
        }
        
        $jsonContent = file_get_contents($jsonPath);
        if (!$jsonContent) {
            return ['code' => -3, 'msg' => '无法读取hms.json文件'];
        }
        
        $hmsData = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['code' => -4, 'msg' => 'hms.json文件格式错误: ' . json_last_error_msg()];
        }
        
        // 确保hmsData是数组
        if (!is_array($hmsData)) {
            return ['code' => -5, 'msg' => 'hms.json文件内容不是有效的数组'];
        }

        $currentTime = time();
        $insertData = [];
        // 处理每个告警消息
        foreach ($param['data']['list'] as $alarm) {
            // 检查必要字段
            if (!isset($alarm['code'], $alarm['device_type'])) {
                continue;
            }
            
            $code = $alarm['code'];
            $deviceType = $alarm['device_type'];
            // 提取domain部分 (格式为domain-type-subtype)
            $domainParts = explode('-', $deviceType);
            $domain = isset($domainParts[0]) ? $domainParts[0] : '';

            // 从函数参数$param中获取sn
            $sn = isset($param['sn']) ? $param['sn'] : '';
            $inTheSky = isset($alarm['in_the_sky']) ? $alarm['in_the_sky'] : 0;
            $args = isset($alarm['args']) ? $alarm['args'] : [];

            // 根据domain拼接文案Key
            $key = '';
            switch ($domain) {
                case '0':
                    // 0: 飞机类
                    if ($inTheSky == 1) {
                        $key = 'fpv_tip_' . $code . '_in_the_sky';
                        // 如果不存在带_in_the_sky后缀的key，则使用不带后缀的
                        if (!isset($hmsData[$key])) {
                            $key = 'fpv_tip_' . $code;
                        }
                    } else {
                        $key = 'fpv_tip_' . $code;
                    }
                    break;
                case '1':
                    // 1: 负载类
                    $key = 'payload_tip_' . $code;
                    break;
                case '2':
                    // 2: 遥控器类
                    $key = 'rc_tip_' . $code;
                    break;
                case '3':
                    // 3: 机场类
                    $key = 'dock_tip_' . $code;
                    break;
                default:
                    // 默认使用飞行器类型
                    $key = 'fpv_tip_' . $code;
                    break;
            }
            
            // 获取告警文案
            // 如果hms.json中找不到对应key，使用默认值'未知告警'
            // 文案可能是纯文本(如'机场配电柜柜门被打开')或包含占位符(如'传感器%index异常')
            $message = isset($hmsData[$key]['zh']) ? $hmsData[$key]['zh'] : '未知告警';

            // 填充文案变量 - 只有当文案中存在对应占位符时才进行替换
            if (strpos($message, '%alarmid') !== false) {
                $message = str_replace('%alarmid', $code, $message);
            }
            
            if (strpos($message, '%index') !== false && isset($args['sensor_index'])) {
                $message = str_replace('%index', $args['sensor_index'] + 1, $message);
            }
            
            if (strpos($message, '%component_index') !== false && isset($args['component_index'])) {
                $componentIndex = $args['component_index'] + 1;
                $componentIndex = max(1, min(2, $componentIndex)); // 限定在1和2之间
                $message = str_replace('%component_index', $componentIndex, $message);
            }
            
            if (strpos($message, '%battery_index') !== false && isset($args['sensor_index'])) {
                $batteryIndex = $args['sensor_index'] == 0 ? '左' : '右';
                $message = str_replace('%battery_index', $batteryIndex, $message);
            }
            
            if (strpos($message, '%dock_cover_index') !== false && isset($args['sensor_index'])) {
                $dockCoverIndex = $args['sensor_index'] == 0 ? '左' : '右';
                $message = str_replace('%dock_cover_index', $dockCoverIndex, $message);
            }
            
            if (strpos($message, '%charging_rod_index') !== false && isset($args['sensor_index'])) {
                $chargingRodIndex = '';
                switch ($args['sensor_index']) {
                    case 0:
                        $chargingRodIndex = '前';
                        break;
                    case 1:
                        $chargingRodIndex = '后';
                        break;
                    case 2:
                        $chargingRodIndex = '左';
                        break;
                    case 3:
                        $chargingRodIndex = '右';
                        break;
                    default:
                        $chargingRodIndex = '未知';
                }
                $message = str_replace('%charging_rod_index', $chargingRodIndex, $message);
            }
            
            // 准备插入数据
            $data = [
                'sn' => $sn,
                'level' => isset($alarm['level']) ? $alarm['level'] : 0,
                'module' => isset($alarm['module']) ? $alarm['module'] : 3, // 默认hms模块
                'in_the_sky' => $inTheSky,
                'code' => $code,
                'device_type' => $deviceType,
                'imminent' => isset($alarm['imminent']) ? $alarm['imminent'] : 0,
                'component_index' => isset($args['component_index']) ? $args['component_index'] : null,
                'sensor_index' => isset($args['sensor_index']) ? $args['sensor_index'] : null,
                'message' => $message,
                'create_time' => $currentTime,
                'update_time' => $currentTime
            ];
           
            $insertData[] = $data;
        }
        // 批量插入数据
        if (!empty($insertData)) {
            Db::name('hmscenter')->insertAll($insertData);
        }
        
        return ['code' => 0, 'msg' => '保存成功', 'count' => count($insertData)];
    }
}
