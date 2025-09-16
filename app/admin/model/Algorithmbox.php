<?php

namespace app\admin\model;

use think\Model;

/**
 * Algorithmbox
 */
class Algorithmbox extends Model
{
    // 表名
    protected $name = 'algorithmbox';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }

    public function appcenter(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Appcenter::class, 'appcenter_id', 'id');
    }
}