<?php

namespace app\admin\model;

use think\Model;

/**
 * Equipment
 */
class Equipment extends Model
{
    // 表名
    protected $name = 'equipment';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function project(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Project::class, 'project_id', 'id');
    }
}