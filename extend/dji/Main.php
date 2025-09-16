<?php

namespace dji;

use think\facade\Log;
use dji\Log as DjiLog;

class Main
{
    protected $airline = null;
    protected $hms = null;
    protected $osd = null;


    public function __construct()
    {
        $this->airline = new Airline();
        $this->hms = new Hms();
        $this->osd = new Osd();
    }

    public function responseEvents($param)
    {
        // if (isset($param['method']) && $param['method'] == 'flighttask_ready') {
        //     $this->airline->flighttaskReady($param);
        // }
        if (isset($param['method']) && $param['method'] == 'hms') {
            $this->hms->save($param);
        }
        if (isset($param['method']) && $param['method'] == 'flighttask_progress') {
            $this->airline->flighttask_progress($param);
        }
        if (isset($param['need_reply']) && $param['need_reply'] == 1) {
            $other = [];
            $other['result'] = 0;
            $this->eventReply($param, $other);
        }
        if (isset($param['method']) && $param['method'] == 'file_upload_callback') {
            $this->airline->file_upload_callback($param);
        }
    }

    public function responseOsd($param)
    {
        // print_r($param);
        if (isset($param['data']['best_link_gateway'])) {
            $this->osd->osdSave($param);
        }
    }

    public function responseStatus($param)
    {
        if (isset($param['method']) && $param['method'] == 'update_topo') {
            $other = [];
            $other['result'] = 0;
            $this->osd->statusReady($param, $other);
        }
    }


    public function responseRequest($param)
    {
        if (isset($param['method']) && $param['method'] == 'flighttask_resource_get') {
            $this->airline->resourceReady($param);
        }
        if (isset($param['method']) && $param['method'] == 'storage_config_get') {
            if ($param['data'] && $param['data']['module'] < 1) {
                $this->airline->storageConfigReady($param);
            }
        }
    }

    public function responseServicesReply($param)
    {
        if (isset($param['method']) && $param['method'] == 'fileupload_list') {
            $djilog = new DjiLog();
            $djilog->saveLog($param);
        }
        if (isset($param['method']) && $param['method'] == 'flighttask_prepare') {
            $djilog = new DjiLog();
            $this->airline->flighttaskErrot($param);
        }
    }

    protected function eventReply($param, $other)
    {
        $data = [];
        $data['bid'] = $param['bid'];
        $data['tid'] = $param['tid'];
        $data['timestamp'] = round(microtime(true) * 1000);
        $data['method'] = $param['method'];
        $data['topic'] = 'thing/product/' . $param['sn'] . '/events_reply';
        $data['data'] = $other;
        $result = publish($data);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
