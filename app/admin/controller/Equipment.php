<?php

namespace app\admin\controller;

use Throwable;
use app\common\controller\Backend;

/**
 * 设备厂商管理
 */
class Equipment extends Backend
{
    /**
     * Equipment模型对象
     * @var object
     * @phpstan-var \app\admin\model\Equipment
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id', 'create_time', 'update_time'];

    protected array $withJoinTable = ['project'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\Equipment();
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是 select 则转发到 select 方法，若未重写该方法，其实还是继续执行 index
        if ($this->request->param('select')) {
            $this->select();
        }

        /**
         * 1. withJoin 不可使用 alias 方法设置表别名，别名将自动使用关联模型名称（小写下划线命名规则）
         * 2. 以下的别名设置了主表别名，同时便于拼接查询参数等
         * 3. paginate 数据集可使用链式操作 each(function($item, $key) {}) 遍历处理
         */
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $where['type'] = 3;
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->visible(['project' => ['name']])
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);
        $list = $res->items();
        foreach ($list as $key => $value) {
            $list[$key]['children'] = $this->model->where('parent_id',$value['id'])->where('type',0)->select();
        }
        $this->success('', [
            'list'   => $list,
            'total'  => $res->total(),
            'remark' => get_route_remark(),
        ]);
    }

    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 添加
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->auth->id;
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                // print_r($data);
                $data['create_time'] = time();
                $data['update_time'] = time();
                $osd = $data['osd'];
                unset($data['osd']);
                $result = $this->model->insertGetId($data);
                if ($result) {
                    if ($data['model'] == '3-2-0') {
                        //入库子设备
                        $child = [
                            'sn' => $osd['sub_device']['device_sn'],
                            'parent_id' => $result,
                            'model' => '0-91-0',
                            'manufacturer' => 0,
                            'type' => 0,
                            'nickname' => $data['nickname'] . '-M3D',
                            'project_id' => $data['project_id'],
                            'firmware_version' => 1,
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    } else {
                        $child = [
                            'sn' => $osd['sub_device']['device_sn'],
                            'parent_id' => $result,
                            'model' => '0-100-0',
                            'manufacturer' => 0,
                            'type' => 0,
                            'nickname' => $data['nickname'] . '-M4D',
                            'project_id' => $data['project_id'],
                            'firmware_version' => 1,
                            'create_time' => time(),
                            'update_time' => time(),
                        ];
                    }
                    $this->model->insert($child);
                }
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }
}
