<?php

namespace app\admin\controller\equipment;

use app\common\controller\Backend;

/**
 * 飞行器市场
 */
class Aircraft extends Backend
{
    /**
     * Aircraft模型对象
     * @var object
     * @phpstan-var \app\admin\model\equipment\Aircraft
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\equipment\Aircraft();
        $this->request->filter('clean_xss');
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */
}