<?php

namespace app\admin\model\equipment;

use think\Model;

/**
 * Aircraft
 */
class Aircraft extends Model
{
    // 表名
    protected $name = 'equipment_aircraft';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}