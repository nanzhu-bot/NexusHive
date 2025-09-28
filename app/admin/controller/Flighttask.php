<?php

namespace app\admin\controller;

use Throwable;
use app\common\controller\Backend;
use dji\Airline;
use app\admin\model\Airline as ModelAirline;

/**
 * 计划任务
 */
class Flighttask extends Backend
{
    /**
     * Flighttask模型对象
     * @var object
     * @phpstan-var \app\admin\model\Flighttask
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array $withJoinTable = ['airline', 'equipment', 'admin'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Flighttask();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->select();
        }

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible(['airline' => ['name'], 'equipment' => ['nickname'], 'admin' => ['username', 'nickname']])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 添加
     */
    public function add(): void
    {
        $admin = $this->auth->getAdmin();
        $airline = new Airline();
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }
            $wayline = ModelAirline::find($data['airline_id'])->toArray();
            if (!$wayline) {
                $this->error('下发失败,未查询到对应航线！');
            }
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $data['bid'] = uuid();
                $data['tid'] = uuid();
                $data['admin_id'] = $admin->id;
                $data['file_url'] = $wayline['kmz'];
                $data['total_point'] = $wayline['point_num'];
                $data['status'] = 'sent';
                if($data['task_type'] < 1){
                    $data['execute_time'] = time();
                }
                $res = $airline->pushTask($data);
                $result = $this->model->save($data);
                if($result){
                    $this->model->commit();
                }
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if($res['code'] > 0){
                $this->model->rollback();
                $this->error($res['msg']);
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 导出
     * @throws Throwable
     */
    public function export(): void
    {
        // 处理跨域请求
        $this->handleCors();
        
        $param = $this->request->param();
        
        // 构建查询条件
        $where = [];
        if (!empty($param['quick_search'])) {
            $where[] = ['name', 'like', '%' . $param['quick_search'] . '%'];
        }
        if (!empty($param['status'])) {
            $where[] = ['status', '=', $param['status']];
        }
        if (!empty($param['task_type'])) {
            $where[] = ['task_type', '=', $param['task_type']];
        }
        if (!empty($param['equipment_id'])) {
            $where[] = ['equipment_id', '=', $param['equipment_id']];
        }
        if (!empty($param['airline_id'])) {
            $where[] = ['airline_id', '=', $param['airline_id']];
        }
        
        // 时间范围查询
        if (!empty($param['create_time'])) {
            $timeRange = explode(' - ', $param['create_time']);
            if (count($timeRange) == 2) {
                $where[] = ['create_time', 'between', [strtotime($timeRange[0]), strtotime($timeRange[1] . ' 23:59:59')]];
            }
        }

        // 查询数据并关联外键表
        $list = $this->model
            ->field('nz_flighttask.*, nz_airline.name as airline_name, nz_equipment.nickname as equipment_name, nz_admin.username as admin_name')
            ->leftJoin('nz_airline', 'nz_flighttask.airline_id = nz_airline.id')
            ->leftJoin('nz_equipment', 'nz_flighttask.equipment_id = nz_equipment.id')
            ->leftJoin('nz_admin', 'nz_flighttask.admin_id = nz_admin.id')
            ->where($where)
            ->order('nz_flighttask.id', 'desc')
            ->select()
            ->toArray();

        // 处理数据格式化
        $exportData = [];
        foreach ($list as $item) {
            // 预处理时间相关字段
            $executeTime = '';
            $endTime = '';
            $taskDuration = '';
            
            // 处理开始执行时间
            if (!empty($item['execute_time'])) {
                $executeTime = strtotime($item['execute_time'],time());
            }
            
            // 处理任务结束时间
            if (!empty($item['end_time'])) {
                $endTime = strtotime($item['end_time'],time());
            }
            
            // 处理任务时长
            if (!empty($item['execute_time']) && !empty($item['end_time'])) {
                $taskDuration = $this->calculateTaskDuration($executeTime, $endTime);
            }
            
            $exportData[] = [
                'ID' => $item['id'],
                '任务名称' => $item['name'] ?? '',
                '业务ID' => $item['bid'],
                '事务ID' => $item['tid'],
                '执行航线' => $item['airline_name'] ?? '',
                '执行设备' => $item['equipment_name'] ?? '',
                '开始执行时间' => $item['execute_time'] ?? '',
                '任务结束时间' => $item['end_time'] ?? '',
                '任务时长' => $taskDuration,
                '任务类型' => $this->getTaskTypeText($item['task_type']),
                '航线文件URL' => $item['file_url'],
                '航线文件签名' => $item['file_fingerprint'],
                '返航高度' => $item['rth_altitude'] ?? '',
                '返航高度模式' => $this->getRthModeText($item['rth_mode']),
                '遥控器失控动作' => $this->getOutOfControlActionText($item['out_of_control_action']),
                '航线失控动作' => $this->getExitWaylineText($item['exit_wayline_when_rc_lost']),
                '航线精度类型' => $this->getWaylinePrecisionText($item['wayline_precision_type']),
                '创建时间' => $item['create_time'] ? date('Y-m-d H:i:s', $item['create_time']) : '',
                '修改时间' => $item['update_time'] ? date('Y-m-d H:i:s', $item['update_time']) : '',
                '执行状态' => $this->getStatusText($item['status']),
                '创建人' => $item['admin_name'] ?? '',
                '错误码' => $item['error_code'],
                '错误原因' => $item['error_msg'],
                '总航点' => $item['total_point'] ?? 0,
                '执行航点' => $item['now_point'] ?? 0,
                '媒体数量' => $item['media_total'] ?? 0,
                '上传数量' => $item['media_now'] ?? 0,
            ];
        }

        // 导出Excel
        $fileName = '飞行任务数据_' . date('YmdHis') . '.xlsx';
        $result = $this->exportToExcel($exportData, $fileName);
        
        if ($result['success']) {
            $this->success('导出成功', $result['data']);
        } else {
            $this->error($result['message']);
        }
    }

    /**
     * 获取任务类型文本
     */
    private function getTaskTypeText($type)
    {
        $types = [
            '0' => '立即任务',
            '1' => '定时任务'
        ];
        return $types[$type] ?? $type;
    }

    /**
     * 获取返航高度模式文本
     */
    private function getRthModeText($mode)
    {
        $modes = [
            '0' => '智能高度',
            '1' => '设定高度'
        ];
        return $modes[$mode] ?? $mode;
    }

    /**
     * 获取遥控器失控动作文本
     */
    private function getOutOfControlActionText($action)
    {
        $actions = [
            '0' => '返航',
            '1' => '悬停',
            '2' => '降落'
        ];
        return $actions[$action] ?? $action;
    }

    /**
     * 获取航线失控动作文本
     */
    private function getExitWaylineText($action)
    {
        $actions = [
            '0' => '继续执行航线任务',
            '1' => '退出航线任务',
            '2' => '执行遥控器失控动作'
        ];
        return $actions[$action] ?? $action;
    }

    /**
     * 获取航线精度类型文本
     */
    private function getWaylinePrecisionText($type)
    {
        $types = [
            '0' => 'GPS 任务',
            '1' => '高精度 RTK 任务'
        ];
        return $types[$type] ?? $type;
    }

    /**
     * 获取执行状态文本
     */
    private function getStatusText($status)
    {
        $statuses = [
            'canceled' => '取消或终止',
            'failed' => '失败',
            'in_progress' => '执行中',
            'ok' => '执行成功',
            'partially_done' => '部分完成',
            'paused' => '暂停',
            'rejected' => '拒绝',
            'sent' => '已下发',
            'timeout' => '超时'
        ];
        return $statuses[$status] ?? $status;
    }

    /**
     * 处理跨域请求
     */
    private function handleCors()
    {
        // 设置允许跨域的响应头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        
        // 处理预检请求
        if ($this->request->method() === 'OPTIONS') {
            exit();
        }
    }

    /**
     * 格式化时间戳
     */
    private function formatTimestamp($timestamp)
    {
        if (empty($timestamp)) {
            return '';
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * 计算任务时长
     */
    private function calculateTaskDuration($startTime, $endTime)
    {
        // 检查开始时间和结束时间是否都存在
        if (empty($startTime) || empty($endTime)) {
            return '';
        }
        
        // 处理字符串类型的时间戳
        if (is_string($startTime)) {
            $startTime = trim($startTime);
            if (!is_numeric($startTime)) {
                return '';
            }
            $startTime = floatval($startTime); 
        }
        if (is_string($endTime)) {
            $endTime = trim($endTime);
            if (!is_numeric($endTime)) {
                return '';
            }
            $endTime = floatval($endTime);
        }
        
        // 如果是毫秒时间戳，转换为秒时间戳
        if ($startTime > 9999999999) {
            $startTime = intval($startTime / 1000);
        }
        if ($endTime > 9999999999) {
            $endTime = intval($endTime / 1000);
        }
        
        // 验证时间戳是否有效
        if ($startTime <= 0 || $endTime <= 0) {
            return '';
        }
        
        // 计算时长差值（秒）
        $duration = $endTime - $startTime;
        
        // 如果时长为负数或0，返回空
        if ($duration <= 0) {
            return '';
        }
        
        // 转换为分秒格式
        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        
        if ($minutes > 0) {
            return $minutes . '分' . $seconds . '秒';
        } else {
            return $seconds . '秒';
        }
    }

    /**
     * 导出数据到Excel
     */
    private function exportToExcel($data, $fileName)
    {
        // 检查是否安装了PhpSpreadsheet
        if (!class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return ['success' => false, 'message' => '请先安装PhpSpreadsheet扩展包'];
        }

        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // 设置表头
            if (!empty($data)) {
                $headers = array_keys($data[0]);
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . '1', $header);
                    $col++;
                }

                // 设置数据
                $row = 2;
                foreach ($data as $item) {
                    $col = 'A';
                    foreach ($item as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    $row++;
                }
            }

            // 确保导出目录存在
            $exportDir = root_path() . 'public/exports/';
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            // 生成文件路径
            $filePath = $exportDir . $fileName;
            
            // 保存文件
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

            // 生成下载链接
            $downloadUrl = request()->domain() . '/exports/' . $fileName;

            return [
                'success' => true,
                'data' => [
                    'download_url' => $downloadUrl,
                    'file_name' => $fileName,
                    'file_size' => filesize($filePath)
                ]
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => '导出失败：' . $e->getMessage()];
        }
    }
}