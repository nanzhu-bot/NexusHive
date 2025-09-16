<?php

namespace app\admin\model\equipment;

use think\Model;

/**
 * Alarm
 */
class Alarm extends Model
{
    // 表名
    protected $name = 'equipment_alarm';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function equipment(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Equipment::class, 'equipment_id', 'id');
    }
}