<?php

namespace app\admin\controller;

use Throwable;
use ba\Terminal;
use think\Response;
use ba\TableManager;
use think\facade\Db;
use think\facade\Cache;
use think\facade\Event;
use app\admin\model\AdminLog;
use app\common\library\Upload;
use app\common\controller\Backend;

class Ajax extends Backend
{
    protected array $noNeedPermission = ['*'];

    /**
     * 无需登录的方法
     * terminal 内部自带验权
     */
    protected array $noNeedLogin = ['terminal'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function upload(): void
    {
        AdminLog::instance()->setTitle(__('upload'));
        $file   = $this->request->file('file');
        $driver = $this->request->param('driver', 'local');
        $topic  = $this->request->param('topic', 'default');
        try {
            $upload     = new Upload();
            $attachment = $upload
                ->setFile($file)
                ->setDriver($driver)
                ->setTopic($topic)
                ->upload(null, $this->auth->id);
            unset($attachment['create_time'], $attachment['quote']);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment ?? []
        ]);
    }

    /**
     * 获取省市区数据
     * @throws Throwable
     */
    public function area(): void
    {
        $this->success('', get_area());
    }

    public function buildSuffixSvg(): Response
    {
        $suffix     = $this->request->param('suffix', 'file');
        $background = $this->request->param('background');
        $content    = build_suffix_svg((string)$suffix, (string)$background);
        return response($content, 200, ['Content-Length' => strlen($content)])->contentType('image/svg+xml');
    }

    /**
     * 获取已脱敏的数据库连接配置列表
     * @throws Throwable
     */
    public function getDatabaseConnectionList(): void
    {
        $quickSearch     = $this->request->get("quickSearch/s", '');
        $connections     = config('database.connections');
        $desensitization = [];
        foreach ($connections as $key => $connection) {
            $connection        = TableManager::getConnectionConfig($key);
            $desensitization[] = [
                'type'     => $connection['type'],
                'database' => substr_replace($connection['database'], '****', 1, strlen($connection['database']) > 4 ? 2 : 1),
                'key'      => $key,
            ];
        }

        if ($quickSearch) {
            $desensitization = array_filter($desensitization, function ($item) use ($quickSearch) {
                return preg_match("/$quickSearch/i", $item['key']);
            });
            $desensitization = array_values($desensitization);
        }

        $this->success('', [
            'list' => $desensitization,
        ]);
    }

    /**
     * 获取表主键字段
     * @param ?string $table
     * @param ?string $connection
     * @throws Throwable
     */
    public function getTablePk(?string $table = null, ?string $connection = null): void
    {
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $table = TableManager::tableName($table, true, $connection);
        if (!TableManager::phinxAdapter(false, $connection)->hasTable($table)) {
            $this->error(__('Data table does not exist'));
        }

        $tablePk = Db::connect(TableManager::getConnection($connection))
            ->table($table)
            ->getPk();
        $this->success('', ['pk' => $tablePk]);
    }

    /**
     * 获取数据表列表
     * @throws Throwable
     */
    public function getTableList(): void
    {
        $quickSearch  = $this->request->get("quickSearch/s", '');
        $connection   = $this->request->request('connection');// 数据库连接配置标识
        $samePrefix   = $this->request->request('samePrefix/b', true);// 是否仅返回项目数据表（前缀同项目一致的）
        $excludeTable = $this->request->request('excludeTable/a', []);// 要排除的数据表数组（表名无需带前缀）

        $outTables = [];
        $dbConfig  = TableManager::getConnectionConfig($connection);
        $tables    = TableManager::getTableList($connection);

        if ($quickSearch) {
            $tables = array_filter($tables, function ($comment) use ($quickSearch) {
                return preg_match("/$quickSearch/i", $comment);
            });
        }

        $pattern = '/^' . $dbConfig['prefix'] . '/i';
        foreach ($tables as $table => $comment) {
            if ($samePrefix && !preg_match($pattern, $table)) continue;

            $table = preg_replace($pattern, '', $table);
            if (!in_array($table, $excludeTable)) {
                $outTables[] = [
                    'table'      => $table,
                    'comment'    => $comment,
                    'connection' => $connection,
                    'prefix'     => $dbConfig['prefix'],
                ];
            }
        }

        $this->success('', [
            'list' => $outTables,
        ]);
    }

    /**
     * 获取数据表字段列表
     * @throws Throwable
     */
    public function getTableFieldList(): void
    {
        $table      = $this->request->param('table');
        $clean      = $this->request->param('clean', true);
        $connection = $this->request->request('connection');
        if (!$table) {
            $this->error(__('Parameter error'));
        }

        $connection = TableManager::getConnection($connection);
        $tablePk    = Db::connect($connection)->name($table)->getPk();
        $this->success('', [
            'pk'        => $tablePk,
            'fieldList' => TableManager::getTableColumns($table, $clean, $connection),
        ]);
    }

    public function changeTerminalConfig(): void
    {
        AdminLog::instance()->setTitle(__('Change terminal config'));
        if (Terminal::changeTerminalConfig()) {
            $this->success();
        } else {
            $this->error(__('Failed to modify the terminal configuration. Please modify the configuration file manually:%s', ['/config/buildadmin.php']));
        }
    }

    public function clearCache(): void
    {
        AdminLog::instance()->setTitle(__('Clear cache'));
        $type = $this->request->post('type');
        if ($type == 'tp' || $type == 'all') {
            Cache::clear();
        } else {
            $this->error(__('Parameter error'));
        }
        Event::trigger('cacheClearAfter', $this->app);
        $this->success(__('Cache cleaned~'));
    }

    /**
     * 同步上传KMZ文件到OSS
     * 用于航线add()和edit()生成KMZ文件后同步上传到OSS
     * @throws Throwable
     */
    public function syncKmzToOss(): void
    {
        AdminLog::instance()->setTitle(__('Sync KMZ to OSS'));
        
        try {
            $localPath = $this->request->param('local_path', ''); // 本地KMZ文件路径
            $fileName = $this->request->param('file_name', ''); // 文件名
            $topic = $this->request->param('topic', 'kmz'); // 存储主题，默认为kmz
            
            // 参数验证
            if (empty($localPath) || empty($fileName)) {
                $this->error(__('Parameter %s can not be empty', ['local_path or file_name']));
            }
            
            // 检查本地文件是否存在
            $fullPath = public_path() . ltrim($localPath, '/');
            if (!file_exists($fullPath)) {
                $this->error(__('Local KMZ file does not exist: %s', [$fullPath]));
            }
            
            // 验证文件格式
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (!in_array($fileExtension, ['kmz', 'kml'])) {
                $this->error(__('File format not supported, only KMZ/KML files are allowed'));
            }
            
            // 创建临时UploadedFile对象进行上传
            $fileInfo = [
                'name' => $fileName,
                'type' => 'application/vnd.google-earth.kmz',
                'tmp_name' => $fullPath,
                'error' => 0,
                'size' => filesize($fullPath)
            ];
            
            // 使用阿里云OSS驱动上传
            $upload = new Upload();
            $upload->setTopic($topic);
            $upload->setDriver('alioss'); // 使用阿里云OSS驱动
            
            // 手动构建文件对象
            $tempFile = new \think\file\UploadedFile($fullPath, $fileName, $fileInfo['type'], $fileInfo['size'], 0);
            $upload->setFile($tempFile);
            
            // 生成OSS保存路径
            $saveName = $this->generateOssSaveName($fileName, $topic);
            
            // 上传到OSS
            $attachment = $upload->upload($saveName, $this->auth->id);
            
            // 计算文件MD5
            $fileMd5 = md5_file($fullPath);
            
            // 返回上传结果
            $result = [
                'status' => true,
                'message' => __('KMZ file synced to OSS successfully'),
                'data' => [
                    'oss_url' => $attachment['url'], // OSS文件URL
                    'local_path' => $localPath, // 本地文件路径
                    'file_name' => $fileName, // 文件名
                    'file_size' => $attachment['size'], // 文件大小
                    'file_md5' => $fileMd5, // 文件MD5
                    'storage' => $attachment['storage'], // 存储驱动
                    'attachment_id' => $attachment['id'] // 附件ID
                ]
            ];
            
            $this->success($result['message'], $result['data']);
            
        } catch (\Exception $e) {
            $this->error(__('Sync KMZ to OSS failed: %s', [$e->getMessage()]));
        }
    }
    
    /**
     * 生成OSS保存路径
     * @param string $fileName 原文件名
     * @param string $topic 主题
     * @return string
     */
    private function generateOssSaveName(string $fileName, string $topic): string
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $baseName = pathinfo($fileName, PATHINFO_FILENAME);
        
        // 生成唯一文件名，避免重复
        $uniqueName = $baseName . '_' . date('YmdHis') . '_' . substr(md5(uniqid()), 0, 8);
        
        // 构建保存路径: topic/年/月/日/文件名.扩展名
        return sprintf(
            '%s/%s/%s/%s/%s.%s',
            $topic,
            date('Y'),
            date('m'),
            date('d'),
            $uniqueName,
            $extension
        );
    }
    
    /**
     * 批量同步KMZ文件到OSS
     * 用于批量处理多个KMZ文件
     * @throws Throwable
     */
    public function batchSyncKmzToOss(): void
    {
        AdminLog::instance()->setTitle(__('Batch sync KMZ to OSS'));
        
        try {
            $files = $this->request->param('files', []); // 文件列表
            $topic = $this->request->param('topic', 'kmz');
            
            if (empty($files) || !is_array($files)) {
                $this->error(__('Parameter %s can not be empty', ['files']));
            }
            
            $results = [];
            $successCount = 0;
            $failCount = 0;
            
            foreach ($files as $file) {
                try {
                    $localPath = $file['local_path'] ?? '';
                    $fileName = $file['file_name'] ?? '';
                    
                    if (empty($localPath) || empty($fileName)) {
                        $results[] = [
                            'file_name' => $fileName,
                            'status' => false,
                            'message' => 'Missing required parameters'
                        ];
                        $failCount++;
                        continue;
                    }
                    
                    // 模拟单个文件上传逻辑
                    $fullPath = public_path() . ltrim($localPath, '/');
                    if (!file_exists($fullPath)) {
                        $results[] = [
                            'file_name' => $fileName,
                            'status' => false,
                            'message' => 'File does not exist'
                        ];
                        $failCount++;
                        continue;
                    }
                    
                    // 执行上传逻辑（简化版）
                    $upload = new Upload();
                    $tempFile = new \think\file\UploadedFile($fullPath, $fileName, 'application/vnd.google-earth.kmz', filesize($fullPath), 0);
                    $upload->setFile($tempFile)->setTopic($topic)->setDriver('alioss');
                    
                    $saveName = $this->generateOssSaveName($fileName, $topic);
                    $attachment = $upload->upload($saveName, $this->auth->id);
                    
                    $results[] = [
                        'file_name' => $fileName,
                        'status' => true,
                        'message' => 'Upload successful',
                        'oss_url' => $attachment['url'],
                        'file_md5' => md5_file($fullPath)
                    ];
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $results[] = [
                        'file_name' => $fileName ?? 'unknown',
                        'status' => false,
                        'message' => $e->getMessage()
                    ];
                    $failCount++;
                }
            }
            
            $this->success(__('Batch sync completed'), [
                'total' => count($files),
                'success' => $successCount,
                'failed' => $failCount,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            $this->error(__('Batch sync KMZ to OSS failed: %s', [$e->getMessage()]));
        }
    }

    /**
     * 终端
     * @throws Throwable
     */
    public function terminal(): void
    {
        (new Terminal())->exec();
    }
}
