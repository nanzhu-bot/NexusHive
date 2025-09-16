<?php

namespace app\common\library;

use Throwable;
use app\admin\model\AdminRule;
use app\admin\model\UserRule;

/**
 * 菜单规则管理类
 */
class Menu
{
    /**
     * @param array      $menu
     * @param int|string $parent   父级规则name或id
     * @param string     $mode     添加模式(规则重复时):cover=覆盖旧菜单,rename=重命名新菜单,ignore=忽略
     * @param string     $position 位置:backend=后台,frontend=前台
     * @return void
     * @throws Throwable
     */
    public static function create(array $menu, int|string $parent = 0, string $mode = 'cover', string $position = 'backend'): void
    {
        $pid        = 0;
        $model      = $position == 'backend' ? new AdminRule() : new UserRule();
        $parentRule = $model->where((is_numeric($parent) ? 'id' : 'name'), $parent)->find();
        if ($parentRule) {
            $pid = $parentRule['id'];
        }
        foreach ($menu as $item) {
            if (!self::requiredAttrCheck($item)) {
                continue;
            }

            // 属性
            $item['status'] = 1;
            if (!isset($item['pid'])) {
                $item['pid'] = $pid;
            }

            $sameOldMenu = $model->where('name', $item['name'])->find();
            if ($sameOldMenu) {
                // 存在相同名称的菜单规则
                if ($mode == 'cover') {
                    $sameOldMenu->save($item);
                } elseif ($mode == 'rename') {
                    $count         = $model->where('name', $item['name'])->count();
                    $item['name']  = $item['name'] . '-CONFLICT-' . $count;
                    $item['path']  = $item['path'] . '-CONFLICT-' . $count;
                    $item['title'] = $item['title'] . '-CONFLICT-' . $count;
                    $sameOldMenu   = $model->create($item);
                } elseif ($mode == 'ignore') {
                    // 忽略同名菜单时，当前 pid 下没有同名菜单，则创建同名新菜单，以保证所有新增菜单的上下级结构
                    $sameOldMenu = $model
                        ->where('name', $item['name'])
                        ->where('pid', $item['pid'])
                        ->find();

                    if (!$sameOldMenu) {
                        $sameOldMenu = $model->create($item);
                    }
                }
            } else {
                $sameOldMenu = $model->create($item);
            }
            if (!empty($item['children'])) {
                self::create($item['children'], $sameOldMenu['id'], $mode, $position);
            }
        }
    }

    /**
     * 删菜单
     * @param string|int $id        规则name或id
     * @param bool       $recursion 是否递归删除子级菜单、是否删除自身，是否删除上级空菜单
     * @param string     $position  位置:backend=后台,frontend=前台
     * @return bool
     * @throws Throwable
     */
    public static function delete(string|int $id, bool $recursion = false, string $position = 'backend'): bool
    {
        if (!$id) {
            return true;
        }
        $model    = $position == 'backend' ? new AdminRule() : new UserRule();
        $menuRule = $model->where((is_numeric($id) ? 'id' : 'name'), $id)->find();
        if (!$menuRule) {
            return true;
        }

        $children = $model->where('pid', $menuRule['id'])->select()->toArray();
        if ($recursion && $children) {
            foreach ($children as $child) {
                self::delete($child['id'], true, $position);
            }
        }

        if (!$children || $recursion) {
            $menuRule->delete();
            self::delete($menuRule->pid, false, $position);
        }
        return true;
    }

    /**
     * 启用菜单
     * @param string|int $id       规则name或id
     * @param string     $position 位置:backend=后台,frontend=前台
     * @return bool
     * @throws Throwable
     */
    public static function enable(string|int $id, string $position = 'backend'): bool
    {
        $model    = $position == 'backend' ? new AdminRule() : new UserRule();
        $menuRule = $model->where((is_numeric($id) ? 'id' : 'name'), $id)->find();
        if (!$menuRule) {
            return false;
        }
        $menuRule->status = 1;
        $menuRule->save();
        return true;
    }

    /**
     * 禁用菜单
     * @param string|int $id       规则name或id
     * @param string     $position 位置:backend=后台,frontend=前台
     * @return bool
     * @throws Throwable
     */
    public static function disable(string|int $id, string $position = 'backend'): bool
    {
        $model    = $position == 'backend' ? new AdminRule() : new UserRule();
        $menuRule = $model->where((is_numeric($id) ? 'id' : 'name'), $id)->find();
        if (!$menuRule) {
            return false;
        }
        $menuRule->status = 0;
        $menuRule->save();
        return true;
    }

    public static function requiredAttrCheck($menu): bool
    {
        $attrs = ['type', 'title', 'name'];
        foreach ($attrs as $attr) {
            if (!array_key_exists($attr, $menu)) {
                return false;
            }
            if (!$menu[$attr]) {
                return false;
            }
        }
        return true;
    }
}