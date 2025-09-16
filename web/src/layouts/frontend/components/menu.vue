<template>
    <el-menu ref="layoutMenuRef" :default-active="state.activeMenu" @select="onSelect">
        <el-menu-item @click="router.push({ name: '/' })" v-blur index="index">
            <Icon v-if="props.showIcon" name="fa fa-home" color="var(--el-text-color-primary)" />
            <template #title>{{ $t('Home') }}</template>
        </el-menu-item>

        <!-- 动态菜单 -->
        <MenuSub :menus="siteConfig.headNav" :show-icon="showIcon" />

        <template v-if="memberCenter.state.open">
            <el-sub-menu v-if="userInfo.isLogin()" @click="$attrs.mode == 'vertical' ? '' : router.push({ name: 'user' })" v-blur index="user-box">
                <template #title>
                    <div class="header-user-box">
                        <img
                            class="header-user-avatar"
                            :class="$attrs.mode == 'vertical' ? 'icon-header-user-avatar' : ''"
                            :src="fullUrl(userInfo.avatar ? userInfo.avatar : '/static/images/avatar.png')"
                            alt=""
                        />
                        {{ userInfo.nickname }}
                    </div>
                </template>

                <el-menu-item @click="router.push({ name: 'user' })" v-blur index="user">
                    <Icon v-if="showIcon" name="fa fa-user-circle" color="var(--el-text-color-primary)" />
                    {{ $t('Member Center') }}
                </el-menu-item>

                <!-- 动态菜单 -->
                <MenuSub :menus="memberCenter.state.navUserMenus" :show-icon="showIcon" />

                <!-- 会员中心菜单 -->
                <MenuSub :menus="memberCenter.state.viewRoutes" :show-icon="showIcon" />

                <el-menu-item @click="userInfo.logout()" v-blur index="user-logout">
                    <Icon v-if="showIcon" name="fa fa-sign-out" color="var(--el-text-color-primary)" />
                    {{ $t('Logout login') }}
                </el-menu-item>
            </el-sub-menu>
            <el-menu-item v-else @click="router.push({ name: 'user' })" v-blur index="user">
                <Icon v-if="showIcon" name="fa fa-user-circle" color="var(--el-text-color-primary)" />
                {{ $t('Member Center') }}
            </el-menu-item>

            <el-sub-menu v-blur index="language-switch" class="language-switch">
                <template #title>
                    <Icon v-if="showIcon" name="local-lang" color="var(--el-text-color-primary)" />
                    {{ $t('Language') }}
                </template>
                <el-menu-item
                    @click="editDefaultLang(item.name)"
                    v-for="item in config.lang.langArray"
                    :key="item.name"
                    :index="'language-switch-' + item.value"
                    class="language-switch"
                >
                    <Icon v-if="showIcon" name="fa fa-circle-o" color="var(--el-text-color-primary)" />
                    {{ item.value }}
                </el-menu-item>
            </el-sub-menu>

            <el-menu-item index="theme-switch" class="theme-switch" :class="$attrs.mode + '-theme-switch'">
                <DarkSwitch @click="toggleDark()" />
            </el-menu-item>
        </template>
    </el-menu>
</template>

<script setup lang="ts">
import { nextTick, reactive } from 'vue'
import { editDefaultLang } from '/@/lang/index'
import { useConfig } from '/@/stores/config'
import { useUserInfo } from '/@/stores/userInfo'
import { useSiteConfig } from '/@/stores/siteConfig'
import { useMemberCenter } from '/@/stores/memberCenter'
import { fullUrl } from '/@/utils/common'
import MenuSub from '/@/layouts/frontend/components/menuSub.vue'
import toggleDark from '/@/utils/useDark'
import DarkSwitch from '/@/layouts/common/components/darkSwitch.vue'
import { onBeforeRouteUpdate, useRoute, useRouter } from 'vue-router'
import type { RouteLocationNormalizedLoaded, RouteRecordRaw } from 'vue-router'
import { layoutMenuRef } from '/@/stores/refs'

const route = useRoute()
const router = useRouter()
const config = useConfig()
const userInfo = useUserInfo()
const siteConfig = useSiteConfig()
const memberCenter = useMemberCenter()

interface Props {
    showIcon?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    showIcon: false,
})

const state = reactive({
    activeMenu: '',
})

/**
 * 设置激活菜单
 */
const setActiveMenu = (route: RouteLocationNormalizedLoaded) => {
    if (route.path == '/') return (state.activeMenu = 'index')

    const menuId = findMenus(route)
    if (menuId) {
        state.activeMenu = 'column-' + menuId
    } else if (route.path.startsWith('/user')) {
        state.activeMenu = 'user'
    }
}

/**
 * 菜单被点击时额外对无需激活的菜单处理（外链、暗黑模式开关、语言切换等）
 * 检查菜单是否需要激活，如果否，还原 state.activeMenu
 */
const onSelect = (index: string) => {
    if (
        noNeedActive(siteConfig.headNav, index) ||
        noNeedActive(memberCenter.state.navUserMenus, index) ||
        noNeedActive(memberCenter.state.viewRoutes, index)
    ) {
        const oldActiveMenu = state.activeMenu
        state.activeMenu = ''
        nextTick(() => {
            state.activeMenu = oldActiveMenu
        })
    }
}

/**
 * 检查一个菜单是否需要激活态
 * @param menus
 * @param index
 */
const noNeedActive = (menus: RouteRecordRaw[], index: string) => {
    if (index.indexOf('language-switch') === 0 || index == 'theme-switch') {
        return true
    }
    return isExternalLink(menus, index)
}

/**
 * 检查一个菜单是否是外站链接，如果是，不要激活
 * @param menus
 * @param index
 */
const isExternalLink = (menus: RouteRecordRaw[], index: string): boolean => {
    for (const key in menus) {
        const columnIndex = `column-${menus[key].meta?.id}`
        if (columnIndex == index) {
            return menus[key].meta?.menu_type == 'link'
        }
        if (menus[key].children?.length) {
            return isExternalLink(menus[key].children!, index)
        }
    }
    return false
}

/**
 * 递归的搜索菜单 Index
 */
const searchMenuIndex = (menus: RouteRecordRaw[], route: RouteLocationNormalizedLoaded): number | false => {
    let find: boolean | number = false
    for (const key in menus) {
        if (menus[key].meta?.id && menus[key].path == route.fullPath) {
            return menus[key].meta.id as number
        }
        if (menus[key].children && menus[key].children?.length) {
            find = searchMenuIndex(menus[key].children, route)
            if (find !== false) return find
        }
    }
    return find
}

/**
 * 从动态菜单（顶栏、会员中心下拉、会员中心菜单）中搜索一个菜单
 */
const findMenus = (route: RouteLocationNormalizedLoaded) => {
    // 顶栏菜单
    const headNavIndex = searchMenuIndex(siteConfig.headNav, route)
    if (headNavIndex !== false) return headNavIndex

    // 会员中心下拉菜单
    const navUserMenuIndex = searchMenuIndex(memberCenter.state.navUserMenus, route)
    if (navUserMenuIndex !== false) return navUserMenuIndex

    // 会员中心菜单
    return searchMenuIndex(memberCenter.state.viewRoutes, route)
}

setActiveMenu(route)
onBeforeRouteUpdate((to) => {
    setActiveMenu(to)
})
</script>

<style scoped lang="scss">
.header-user-box {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    .header-user-avatar {
        width: 16px;
        height: 16px;
        margin-right: 4px;
        border-radius: 50%;
    }
    .icon-header-user-avatar {
        margin-left: 4px;
        margin-right: 6px;
    }
}
.el-sub-menu .icon,
.el-menu-item .icon {
    vertical-align: middle;
    margin-right: 2px;
    width: 24px;
    text-align: center;
    flex-shrink: 0;
}
.is-active > .icon {
    color: var(--el-menu-active-color) !important;
}
.el-menu {
    border-bottom: none;
    border-right: none;
    .theme-switch.is-active,
    .language-switch.is-active {
        border-bottom: none;
        :deep(.el-sub-menu__title) {
            border-bottom: none;
        }
    }
}
.theme-switch {
    --el-menu-hover-bg-color: none;
    padding-right: 0;
}
.vertical-theme-switch {
    .theme-toggle-content {
        padding: 0;
    }
}
.theme-toggle-content {
    padding-right: 0;
}
</style>
