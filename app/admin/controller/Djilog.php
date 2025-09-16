<?php

namespace app\admin\controller;

use app\common\controller\Backend;

/**
 * 日志管理
 */
class Djilog extends Backend
{
    /**
     * Djilog模型对象
     * @var object
     * @phpstan-var \app\admin\model\Djilog
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Djilog();
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}