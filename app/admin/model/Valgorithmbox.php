<?php

namespace app\admin\model;

use think\Model;

/**
 * Valgorithmbox
 */
class Valgorithmbox extends Model
{
    // 表名
    protected $name = 'valgorithmbox';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function getContentAttr($value): string
    {
        return !$value ? '' : htmlspecialchars_decode($value);
    }
}