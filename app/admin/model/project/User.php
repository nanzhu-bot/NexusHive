<?php

namespace app\admin\model\project;

use think\Model;

/**
 * User
 */
class User extends Model
{
    // 表名
    protected $name = 'project_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = true;


    public function project(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Project::class, 'project_id', 'id');
    }

    public function admin(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(\app\admin\model\Admin::class, 'admin_id', 'id');
    }
}