<?php

namespace app\admin\validate;

use think\Validate;

class AdminGroup extends Validate
{
    protected $failException = true;

    protected $rule = [
        'name'  => 'require',
        'rules' => 'require',
    ];

    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];

    /**
     * 字段描述
     */
    protected $field = [
    ];

    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name', 'rules'],
        'edit' => ['name', 'rules'],
    ];

    public function __construct()
    {
        $this->field   = [
            'name' => __('name'),
        ];
        $this->message = [
            'rules' => __('Please select rules'),
        ];
        parent::__construct();
    }
}