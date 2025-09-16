<?php

namespace app\admin\model;

use think\Model;

/**
 * Media
 */
class Media extends Model
{
    // 表名
    protected $name = 'media';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;


    public function getGimbalYawDegreeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getAbsoluteAltitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getRelativeAltitudeAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getLatAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }

    public function getLngAttr($value): ?float
    {
        return is_null($value) ? null : (float)$value;
    }
}