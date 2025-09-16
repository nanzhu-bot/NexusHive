<?php

namespace app\admin\model;

use think\Model;

/**
 * Flighttask
 */
class Flighttask extends Model
{
    // 表名
    protected $name = 'flighttask';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;

    // 字段类型转换
    protected $type = [
        'execute_time' => 'timestamp:Y-m-d H:i:s',
        'end_time'     => 'timestamp:Y-m-d H:i:s',
    ];


    public function airline(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Airline::class, 'airline_id', 'id');
    }

    public function equipment(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Equipment::class, 'equipment_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }
}