<?php

namespace app\admin\model\airline;

use think\Model;

/**
 * Floder
 */
class Floder extends Model
{
    // 表名
    protected $name = 'airline_floder';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function project(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Project::class, 'project_id', 'id');
    }
}