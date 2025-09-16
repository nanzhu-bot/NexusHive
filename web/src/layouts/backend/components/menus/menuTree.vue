<template>
    <template v-for="(menu, index) in props.menus" :key="getMenuKey(menu)">
        <template v-if="menu.children && menu.children.length > 0">
            <el-tooltip class="box-item" effect="dark" :content="menu.meta?.title" placement="right">
                <el-sub-menu @click="onClickSubMenu(menu)" :index="getMenuKey(menu)" :key="getMenuKey(menu)">
                    <template #title>
                        <!-- <Icon :color="config.getColorVal('menuColor')" :name="menu.meta?.icon ? menu.meta?.icon : config.layout.menuDefaultIcon" /> -->
                        <img :src="menu.meta?.image" alt="" style="width: 20px; height: 20px" />
                        <span>{{ menu.meta?.title ? menu.meta?.title : $t('noTitle') }}</span>
                    </template>
                    <MenuTree :extends="{ ...props.extends, level: props.extends.level + 1 }" :menus="menu.children" />
                </el-sub-menu>
            </el-tooltip>
        </template>
        <template v-else>
            <el-tooltip class="box-item" effect="dark" :content="menu.meta?.title" placement="right">
                <el-menu-item @click="onClickMenu(menu)" :index="getMenuKey(menu)" :key="getMenuKey(menu)">
                    <Icon
                        v-if="!menu.meta?.image"
                        :color="config.getColorVal('menuColor')"
                        :name="menu.meta?.icon ? menu.meta?.icon : config.layout.menuDefaultIcon"
                    />
                    <img v-else :src="menu.meta?.image" alt="" style="width: 20px; height: 20px" />
                    <span>{{ menu.meta?.title ? menu.meta?.title : $t('noTitle') }}</span>
                </el-menu-item>
            </el-tooltip>
        </template>
        <div v-if="index === 1 && props.isBorder" class="menu-border"></div>
    </template>
</template>

<script setup lang="ts">
import { ElNotification } from 'element-plus'
import { useI18n } from 'vue-i18n'
import type { RouteRecordRaw } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { getFirstRoute, getMenuKey, onClickMenu } from '/@/utils/router'

const { t } = useI18n()
const config = useConfig()

interface Props {
    menus: RouteRecordRaw[]
    extends?: {
        level: number
        [key: string]: any
    }
    isBorder?: boolean
}
const props = withDefaults(defineProps<Props>(), {
    menus: () => [],
    extends: () => {
        return {
            level: 1,
        }
    },
    isBorder: false,
})

/**
 * sub-menu-item 被点击 - 用于单栏布局和双栏布局
 * 顶栏菜单：点击时打开第一个菜单
 * 侧边菜单（若有）：点击只展开收缩
 *
 * sub-menu-item 被点击时，也会触发到 menu-item 的点击事件，由 el-menu 内部触发，无法很好的排除，在此检查 level 值
 */
const onClickSubMenu = (menu: RouteRecordRaw) => {
    if (props.extends?.position == 'horizontal' && props.extends.level <= 1 && menu.children?.length) {
        const firstRoute = getFirstRoute(menu.children)
        if (firstRoute) {
            onClickMenu(firstRoute)
        } else {
            ElNotification({
                type: 'error',
                message: t('utils.No child menu to jump to!'),
            })
        }
    }
}
</script>

<style scoped lang="scss">
.el-menu-item,
.el-sub-menu__title {
    justify-content: center;
}

.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 5px;
    width: 24px;
    text-align: center;
    flex-shrink: 0;
}
.is-active > .icon {
    color: var(--el-menu-active-color) !important;
}
.el-menu-item.is-active {
    background-color: v-bind('config.getColorVal("menuActiveBackground")');
}
.menu-border {
    width: calc(100% - 20px);
    height: 1px;
    background-color: #00000026;
    margin: 10px 10px;
}
</style>
