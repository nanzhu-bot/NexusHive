<?php

namespace app\admin\model;

use think\Model;

/**
 * Flightosd
 */
class Flightosd extends Model
{
    // 表名
    protected $name = 'flightosd';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getLatitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getLongitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }
}