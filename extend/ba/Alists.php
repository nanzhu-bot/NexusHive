<?php
namespace ba;

class Alists
{
    protected $url = 'https://sts.aliyuncs.com';
    protected $accessKeySecret = '';
    protected $accessKeyId = '';
    protected $roleArn = '';//指定角色的 ARN ，角色策略权限
    protected $roleSessionName = '';//用户自定义参数。此参数用来区分不同的 token，可用于用户级别的访问审计。格式：^[a-zA-Z0-9\.@\-_]+$
    protected $durationSeconds = '1800';//指定的过期时间
    protected $type = '';//方便调用时获取不同的权限
 
    public function __construct($type)
    {
        $this->type = $type;
        $this->setRoleArn();
    }
 
    public function sts()
    {
        $action = 'AssumeRole';//通过扮演角色接口获取令牌
        date_default_timezone_set('UTC');
        $param = array(
            'Format'           => 'JSON',
            'Version'          => '2015-04-01',
            'AccessKeyId'      => $this->accessKeyId,
            'SignatureMethod'  => 'HMAC-SHA1',
            'SignatureVersion' => '1.0',
            'SignatureNonce'   => $this->getRandChar(8),
            'Action'           => $action,
            'RoleArn'          => $this->roleArn,
            'RoleSessionName'  => $this->roleSessionName,
            'DurationSeconds'  => $this->durationSeconds,
            'Timestamp'        => date('Y-m-d') . 'T' . date('H:i:s') . 'Z'
            //'Policy'=>'' //此参数可以限制生成的 STS token 的权限，若不指定则返回的 token 拥有指定角色的所有权限。
        );
        $param['Signature'] = $this->computeSignature($param, 'POST');
        
        $res = $this->Curl_request($this->url, 'POST', [], $param);//curl post请求

        if ($res) {
            return self::_render($res);
        } else {
            return [];
        }
    }
 
    private static function _render($res)
    {
        $res = json_decode($res, true);
        if (empty($res['Credentials'])) {
            return [];
        } else {
            return [
                'access_key_secret' => $res['Credentials']['AccessKeySecret'] ?? '',
                'access_key_id'     => $res['Credentials']['AccessKeyId'] ?? '',
                'expire'      => strtotime($res['Credentials']['Expiration']) ?? '',
                'security_token'   => $res['Credentials']['SecurityToken'] ?? '',
            ];
        }
    }
 
    protected function computeSignature($parameters, $setMethod)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = $setMethod . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $this->getSignature($stringToSign, $this->accessKeySecret . '&');
 
        return $signature;
    }
 
    public function getSignature($source, $accessSecret)
    {
        return base64_encode(hash_hmac('sha1', $source, $accessSecret, true));
    }
 
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
 
    public function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
 
        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol[rand(0, $max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
 
        return $str;
    }
 
    protected function setRoleArn()
    {
        if ($this->type == 'djiapi') {//根据入参使用不同的策略，当然这里还可以有其他写法兼容更多的策略的情况
            $this->roleArn = 'acs:ram::1889303047275942:role/oss';
        }
    }

    /**
     * 多种请求方法封装
     * 
     * @param string   $url      请求地址
     * @param string   $method   请求方式
     * @param array    $header   请求头
     * @param array    $data     请求体
     * 
     * @return mixd 
     */
    function Curl_request($url, $method = 'POST', $header = ["Content-type:application/json;charset=utf-8", "Accept:application/json"], $data = [])
    {
        
        $method = strtoupper($method);
        //初始化
        $ch = curl_init();
        //设置桥接(抓包)
        //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8888');
        //设置请求地址
        curl_setopt($ch, CURLOPT_URL, $url);
        // 检查ssl证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // 从检查本地证书检查是否ssl加密
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $url);
        //设置请求方法
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        //设置请求头
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        //设置请求数据
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //设置curl_exec()的返回值以字符串返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch); 
        return $res;
    }
}