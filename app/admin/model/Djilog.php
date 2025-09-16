<?php

namespace app\admin\model;

use think\Model;

/**
 * Djilog
 */
class Djilog extends Model
{
    // 表名
    protected $name = 'djilog';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 字段类型转换
    protected $type = [
        'start_time' => 'timestamp:Y-m-d H:i:s',
        'end_time'   => 'timestamp:Y-m-d H:i:s',
    ];

}