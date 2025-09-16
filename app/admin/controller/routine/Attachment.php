<?php

namespace app\admin\controller\routine;

use Throwable;
use app\common\controller\Backend;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends Backend
{
    /**
     * @var object
     * @phpstan-var AttachmentModel
     */
    protected object $model;

    protected string|array $quickSearchField = 'name';

    protected array $withJoinTable = ['admin', 'user'];

    protected string|array $defaultSortField = 'last_upload_time,desc';

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new AttachmentModel();
    }

    /**
     * 删除
     * @throws Throwable
     */
    public function del(): void
    {
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $ids     = $this->request->param('ids/a', []);
        $where[] = [$this->model->getPk(), 'in', $ids];
        $data    = $this->model->where($where)->select();

        $count = 0;
        try {
            foreach ($data as $v) {
                $count += $v->delete();
            }
        } catch (Throwable $e) {
            $this->error(__('%d records and files have been deleted', [$count]) . $e->getMessage());
        }
        if ($count) {
            $this->success(__('%d records and files have been deleted', [$count]));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}