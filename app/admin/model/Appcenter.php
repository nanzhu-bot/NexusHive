<?php

namespace app\admin\model;

use think\Model;

/**
 * Appcenter
 */
class Appcenter extends Model
{
    // 表名
    protected $name = 'appcenter';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}