<?php

namespace app\admin\controller\equipment;

use app\common\controller\Backend;

/**
 * 负载市场
 */
class Load extends Backend
{
    /**
     * Load模型对象
     * @var object
     * @phpstan-var \app\admin\model\equipment\Load
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\equipment\Load();
        $this->request->filter('clean_xss');
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}