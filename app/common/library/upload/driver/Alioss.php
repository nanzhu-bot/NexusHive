<?php

namespace app\common\library\upload\driver;

use Throwable;
use OSS\OssClient;
use OSS\Core\OssException;
use think\file\UploadedFile;
use InvalidArgumentException;
use think\exception\FileException;
use OSS\Http\RequestCore_Exception;
use app\common\library\upload\Driver;

/**
 * 上传到阿里云存储的驱动
 * @see Driver
 */
class Alioss extends Driver
{
    protected array $options = [];

    /**
     * @throws Throwable
     */
    public function __construct(array $options = [])
    {
        $this->options = get_sys_config('', 'upload');
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }

        if (!$this->options['upload_access_id'] || !$this->options['upload_secret_key'] || !$this->options['upload_bucket']) {
            throw new InvalidArgumentException('Configure the upload parameters of Alioss Cloud storage first!');
        }
    }

    public function getClient(): OssClient
    {
        return new OssClient($this->options['upload_access_id'], $this->options['upload_secret_key'], 'https://' . $this->options['upload_url'] . '.aliyuncs.com');
    }

    /**
     * 将保存名处理为阿里云兼容的格式
     */
    public function handleSaveName(string $saveName): string
    {
        $saveName = str_replace([full_url(), $this->url('')], '', $saveName);
        $saveName = str_replace('\\', '/', $saveName);
        return ltrim($saveName, '/');
    }

    /**
     * 保存文件
     * @param UploadedFile $file
     * @param string       $saveName
     * @return bool
     * @throws Throwable
     */
    public function save(UploadedFile $file, string $saveName): bool
    {
        $saveName  = $this->handleSaveName($saveName);
        $OssClient = $this->getClient();
        $OssClient->uploadFile($this->options['upload_bucket'], $saveName, $file->getPathname());
        return true;
    }

    /**
     * 删除文件
     * @param string $saveName
     * @return bool
     */
    public function delete(string $saveName): bool
    {
        try {
            $saveName  = $this->handleSaveName($saveName);
            $OssClient = $this->getClient();
            $OssClient->deleteObject($this->options['upload_bucket'], $saveName);
        } catch (Throwable $e) {
            throw new FileException($e->getMessage());
        }
        return true;
    }

    /**
     * 获取资源 URL 地址
     * @param string      $saveName 资源保存名称
     * @param string|bool $domain   是否携带域名 或者直接传入域名
     * @param string      $default  默认值
     * @return string
     */
    public function url(string $saveName, bool|string $domain = true, string $default = ''): string
    {
        if ($domain === true) {
            $bucketUrl = 'https://' . $this->options['upload_bucket'] . '.' . $this->options['upload_url'] . '.aliyuncs.com';
            $domain    = $this->options['upload_cdn_url'] ?: $bucketUrl;
        } elseif ($domain === false) {
            $domain = '';
        }

        $saveName = $saveName ?: $default;
        if (!$saveName) return $domain;

        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        if (preg_match('/^http(s)?:\/\//', $saveName) || preg_match($regex, $saveName) || $domain === false) {
            return $saveName;
        }
        return str_replace('\\', '/', $domain . $saveName);
    }

    /**
     * 文件是否存在
     * @param string $saveName
     * @return bool
     */
    public function exists(string $saveName): bool
    {
        try {
            $saveName  = $this->handleSaveName($saveName);
            $OssClient = $this->getClient();
            $OssClient->getObjectMeta($this->options['upload_bucket'], $saveName);
        } catch (OssException|RequestCore_Exception) {
            return false;
        }
        return true;
    }
}