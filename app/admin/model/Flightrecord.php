<?php

namespace app\admin\model;

use think\Model;

/**
 * Flightrecord
 */
class Flightrecord extends Model
{
    // 表名
    protected $name = 'flightrecord';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getBreakPointProgressAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getBreakPointLatitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getBreakPointLongitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getBreakPointHeightAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function flight(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Flighttask::class, 'flight_id', 'bid');
    }
}