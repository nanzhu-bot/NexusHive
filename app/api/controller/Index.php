<?php

namespace app\api\controller;

use app\admin\model\Hms;
use ba\Tree;
use Throwable;
use think\facade\Db;
use think\facade\Config;
use app\common\controller\Frontend;
use app\common\library\token\TokenExpirationException;
use ba\Alists;
use dji\Log;
use think\facade\Cache;

class Index extends Frontend
{
    protected array $noNeedLogin = ['index','getAli','setLog','aaa','eeee','osd'];

    public function initialize(): void
    {
        parent::initialize();
    }

    /**
     * 前台和会员中心的初始化请求
     * @throws Throwable
     */
    public function index(): void
    {
        $menus = [];
        if ($this->auth->isLogin()) {
            $rules     = [];
            $userMenus = $this->auth->getMenus();

            // 首页加载的规则，验权，但过滤掉会员中心菜单
            foreach ($userMenus as $item) {
                if ($item['type'] == 'menu_dir') {
                    $menus[] = $item;
                } elseif ($item['type'] != 'menu') {
                    $rules[] = $item;
                }
            }
            $rules = array_values($rules);
        } else {
            // 若是从前台会员中心内发出的请求，要求必须登录，否则会员中心异常
            $requiredLogin = $this->request->get('requiredLogin/b', false);
            if ($requiredLogin) {

                // 触发可能的 token 过期异常
                try {
                    $token = get_auth_token(['ba', 'user', 'token']);
                    $this->auth->init($token);
                } catch (TokenExpirationException) {
                    $this->error(__('Token expiration'), [], 409);
                }

                $this->error(__('Please login first'), [
                    'type' => $this->auth::NEED_LOGIN
                ], $this->auth::LOGIN_RESPONSE_CODE);
            }

            $rules = Db::name('user_rule')
                ->where('status', 1)
                ->where('no_login_valid', 1)
                ->where('type', 'in', ['route', 'nav', 'button'])
                ->order('weigh', 'desc')
                ->select()
                ->toArray();
            $rules = Tree::instance()->assembleChild($rules);
        }

        $this->success('', [
            'site'             => [
                'siteName'     => get_sys_config('site_name'),
                'version'      => get_sys_config('version'),
                'cdnUrl'       => full_url(),
                'upload'       => keys_to_camel_case(get_upload_config(), ['max_size', 'save_name', 'allowed_suffixes', 'allowed_mime_types']),
                'recordNumber' => get_sys_config('record_number'),
                'cdnUrlParams' => Config::get('buildadmin.cdn_url_params'),
            ],
            'openMemberCenter' => Config::get('buildadmin.open_member_center'),
            'userInfo'         => $this->auth->getUserInfo(),
            'rules'            => $rules,
            'menus'            => $menus,
        ]);
    }

    public function getAli(){
        $ali = new Alists('djiapi');
        $res = $ali->sts();
        print_r($res);
    }

    public function setLog(){
        $log = new Log();
        $res = $log->uploadLog('7CTXN3S00B08GE');
    }

    public function aaa(){
        // try {
            // 访问指定URL获取JSON数据
            $url = 'https://terra-1-g.djicdn.com/fee90c2e03e04e8da67ea6f56365fc76/SDK%20%E6%96%87%E6%A1%A3/CloudAPI/hms.json';
            
            // 初始化cURL
            $ch = curl_init();
            
            // 设置cURL选项
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10秒超时
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用SSL证书验证
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            // 执行请求
            $responseBody = curl_exec($ch);
            $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            // 检查是否有错误
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                return $this->error('cURL错误: ' . $curlError);
            }
            
            // 检查响应状态
            if ($responseCode != 200) {
                return $this->error('请求失败，状态码: ' . $responseCode);
            }
            
            // 获取JSON数据并转换为数组
            $jsonData = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->error('JSON解析错误: ' . json_last_error_msg());
            }
            
            if (empty($jsonData)) {
                return $this->error('获取的数据为空');
            }
            
            // 为数组中的每个成员添加name键
            $result = [];
            foreach ($jsonData as $key => $value) {
                if (is_array($value)) {
                    $value['name'] = $key;
                } else {
                    // 如果不是数组，创建一个包含原值和name的新数组
                    $value = [
                        'value' => $value,
                        'name' => $key
                    ];
                }
                $result[] = $value;
            }
            $hms = new Hms();
            foreach($result as $key => $item){
                $data = [];
                $data['name'] = $item['name'];
                if(isset($item['en'])){
                    $data['en'] = $item['en'];
                }
                if(isset($item['zh'])){
                    $data['zh'] = $item['zh'];
                }
                // $hms->insert($data);
            }
            // $s = $hms->saveAll($result);
            // return $this->success('处理成功', $s);
        // } catch (\Exception $e) {
        //     return $this->error('处理异常: ' . $e->getMessage());
        // }
    }

    public function eeee(){
       $data = [];
       $data['topic'] = 'aaa/bbb';
       $data['data'] = [
           'a' => 1,
           'b' => 2,
       ];
       publish($data);
    }

    public function osd(): void
    {
        //查询设备数据
        $eq_list = Db::name('equipment')->select();
        //循环获取所有历史数据并叠加
        $total_flight_distance = 0;
        $total_flight_time = 0;
        $total_flight_sorties = 0;
        $total_airline = Db::name('airline')->count();
        foreach($eq_list as $key => $eq){
            $now_sn_flight_distance = Cache::get($eq['sn'].'_total_flight_distance',0);
            $total_flight_distance += $now_sn_flight_distance;
            $now_sn_flight_time = Cache::get($eq['sn'].'_total_flight_time',0);
            $total_flight_time += $now_sn_flight_time;
            $now_sn_flight_sorties = Cache::get($eq['sn'].'_total_flight_sorties',0);
            $total_flight_sorties += $now_sn_flight_sorties;
        }
        //历史数据统计
        $this->success('返回成功', [
            'total_flight_distance' => number_format($total_flight_distance / 1000,2) . 'km',
            'total_flight_time' => number_format($total_flight_time / 60 / 60,2) . 'h',
            'total_flight_sorties' => $total_flight_sorties,
            'total_airline' => $total_airline,
        ]);
    }
}