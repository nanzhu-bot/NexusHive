<?php

namespace app\admin\controller;

use Throwable;
use app\common\controller\Backend;
use ZipArchive;
use modules\alioss\Alioss;

/**
 * 航线管理
 */
class Airline extends Backend
{
    /**
     * Airline模型对象
     * @var object
     * @phpstan-var \app\admin\model\Airline
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array $withJoinTable = ['project'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Airline();
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
            ->visible(['project' => ['name']])
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
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id; 
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                $endpoint = 'https://djiapis.oss-cn-chengdu.aliyuncs.com';
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                if (isset($data['template']) && isset($data['wayline'])) {
                     // 新增KMZ打包逻辑
                    // $templatePath = app()->getRootPath() . '/public' . $data['template'];
                    // $waylinePath = app()->getRootPath() . '/public' . $data['wayline'];
                    $templatePath = $endpoint . $data['template'];
                    $waylinePath = $endpoint . $data['wayline'];
                    // 创建临时目录和wpmz子文件夹
                    $tempDir = app()->getRuntimePath() . 'temp/' . md5(time());
                    $wpmzDir = $tempDir . '/wpmz';
                    mkdir($wpmzDir, 0755, true);
                    
                    // 复制文件到wpmz文件夹
                    copy($templatePath, $wpmzDir . '/template.kml');
                    copy($waylinePath, $wpmzDir . '/waylines.wpml');
                    
                    // 创建ZIP压缩包
                    $zipPath = $tempDir . '/package.zip';
                    $zip = new ZipArchive();
                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                        // 保持wpmz文件夹结构
                        $zip->addFile($wpmzDir . '/template.kml', 'wpmz/template.kml');
                        $zip->addFile($wpmzDir . '/waylines.wpml', 'wpmz/waylines.wpml');
                        $zip->close();
                        
                        // 重命名为KMZ
                        $kmzPath = app()->getRootPath() . 'public/uploads/kmz/' . $data['name'] . '.kmz';
                        // mkdir(dirname($kmzPath), 0755, true);
                        rename($zipPath, $kmzPath);
                        
                        // 保存路径和MD5 
                        $data['kmz'] = '/kmz/' . $data['name'] .'.kmz';
                        $data['kmz_md5'] = md5_file($kmzPath);
                        
                        // 上传KMZ文件到阿里云OSS
                        // 上传KMZ文件到阿里云OSS
                        try {
                            $uploadConfig = get_sys_config('', 'upload');
                            if ($uploadConfig['upload_mode'] == 'alioss') {
                                $ossClient = new \OSS\OssClient(
                                    $uploadConfig['upload_access_id'],
                                    $uploadConfig['upload_secret_key'],
                                    $uploadConfig['upload_url'] . '.aliyuncs.com'
                                );
                                $ossPath = 'kmz/' . $data['name'] . '.kmz';
                                $ossClient->uploadFile($uploadConfig['upload_bucket'], $ossPath, $kmzPath);
                            }
                        } catch (Throwable $e) {
                            // OSS上传失败不影响主流程，记录日志即可
                            trace('OSS上传失败: ' . $e->getMessage(), 'error');
                        }
                    } else {
                        $this->error(__('No rows were added'));
                    }
                    
                }
                if(is_array($data['kmz_json'])){
                    $data['kmz_json'] = json_encode($data['kmz_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif(is_string($data['kmz_json'])) {
                    // 如果是字符串，先解码HTML实体，再重新编码
                    $decoded = html_entity_decode($data['kmz_json'], ENT_QUOTES, 'UTF-8');
                    // 尝试解析为数组，如果成功则重新编码
                    $jsonArray = json_decode($decoded, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonArray)) {
                        $data['kmz_json'] = json_encode($jsonArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        // 如果不是有效的JSON，直接使用解码后的字符串
                        $data['kmz_json'] = $decoded;
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
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
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        
        $endpoint = 'https://djiapis.oss-cn-chengdu.aliyuncs.com';
        $pk  = $this->model->getPk();
        $id  = $this->request->param($pk);
        $row = $this->model->find($id);
        
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data   = $this->excludeFields($data);
            
            // 处理可能存在HTML实体转义的字段
            if (isset($data['kmz_json']) && is_string($data['kmz_json'])) {
                $data['kmz_json'] = html_entity_decode($data['kmz_json'], ENT_QUOTES, 'UTF-8');
            }
            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $data[$pk] = $row[$pk];
                        $validate->check($data);
                    }
                }

                // 新增KMZ打包逻辑（仅在template或wayline变更时执行）
                if (isset($data['template']) || isset($data['wayline'])) {
                    $templatePath = $endpoint . $data['template'];
                    $waylinePath = $endpoint . $data['wayline'];
                    $templatePath = $endpoint . ($data['template'] ?? $row['template']);
                    $waylinePath = $endpoint . ($data['wayline'] ?? $row['wayline']);
                    
                    // 创建临时目录和wpmz子文件夹
                    $tempDir = app()->getRuntimePath() . 'temp/' . md5(time());
                    $wpmzDir = $tempDir . '/wpmz';
                    mkdir($wpmzDir, 0755, true);
                    
                    // 复制文件到wpmz文件夹
                    copy($templatePath, $wpmzDir . '/template.kml');
                    copy($waylinePath, $wpmzDir . '/waylines.wpml');
                    
                    // 创建ZIP压缩包
                    $zipPath = $tempDir . '/package.zip';
                    $zip = new ZipArchive();
                    if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                        // 保持wpmz文件夹结构
                        $zip->addFile($wpmzDir . '/template.kml', 'wpmz/template.kml');
                        $zip->addFile($wpmzDir . '/waylines.wpml', 'wpmz/waylines.wpml');
                        $zip->close();
                        
                        // 重命名为KMZ
                        $kmzPath = app()->getRootPath() . 'public/uploads/kmz/' . $data['name'] .'.kmz';
                        rename($zipPath, $kmzPath);
                        
                        // 更新路径和MD5
                        $data['kmz'] = '/kmz/' . $data['name'] .'.kmz';
                        $data['kmz_md5'] = md5_file($kmzPath);
                        
                        // 上传KMZ文件到阿里云OSS
                        // 上传KMZ文件到阿里云OSS
                        try {
                            $uploadConfig = get_sys_config('', 'upload');
                            if ($uploadConfig['upload_mode'] == 'alioss') {
                                $ossClient = new \OSS\OssClient(
                                    $uploadConfig['upload_access_id'],
                                    $uploadConfig['upload_secret_key'],
                                    $uploadConfig['upload_url'] . '.aliyuncs.com'
                                );
                                $ossPath = 'kmz/' . $data['name'] . '.kmz';
                                $ossClient->uploadFile($uploadConfig['upload_bucket'], $ossPath, $kmzPath);
                            }
                        } catch (Throwable $e) {
                            // OSS上传失败不影响主流程，记录日志即可
                            trace('OSS上传失败: ' . $e->getMessage(), 'error');
                        }
                    }
                }
                if(is_array($data['kmz_json'])){
                    $data['kmz_json'] = json_encode($data['kmz_json'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } elseif(is_string($data['kmz_json'])) {
                    // 如果是字符串，先解码HTML实体，再重新编码
                    $decoded = html_entity_decode($data['kmz_json'], ENT_QUOTES, 'UTF-8');
                    // 尝试解析为数组，如果成功则重新编码
                    $jsonArray = json_decode($decoded, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($jsonArray)) {
                        $data['kmz_json'] = json_encode($jsonArray, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    } else {
                        // 如果不是有效的JSON，直接使用解码后的字符串
                        $data['kmz_json'] = $decoded;
                    }
                }
                if(isset($data['airline_floder_id'])){
                    unset($data['airline_floder_id']);
                }
                if(isset($data['project_id'])){
                    unset($data['project_id']);
                }
                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }
        }

        $this->success('', [
            'row' => $row
        ]);
    }
}