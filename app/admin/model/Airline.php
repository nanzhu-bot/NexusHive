<?php

namespace app\admin\model;

use think\Model;

/**
 * Airline
 */
class Airline extends Model
{
    // 表名
    protected $name = 'airline';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function project(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Project::class, 'project_id', 'id');
    }

    public function airlineFloder(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\airline\Floder::class, 'airline_floder_id', 'id');
    }
}