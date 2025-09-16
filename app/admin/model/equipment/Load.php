<?php

namespace app\admin\model\equipment;

use think\Model;

/**
 * Load
 */
class Load extends Model
{
    // 表名
    protected $name = 'equipment_load';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}