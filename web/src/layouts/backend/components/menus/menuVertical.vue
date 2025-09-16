<template>
    <el-scrollbar ref="layoutMenuScrollbarRef" class="vertical-menus-scrollbar">
        <el-menu
            class="layouts-menu-vertical"
            :collapse-transition="false"
            :unique-opened="config.layout.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="true"
            ref="layoutMenuRef"
        >
            <MenuTree :menus="menuList" :is-border="true" />
            <!-- <MenuTree :menus="navTabs.state.tabsViewRoutes" /> -->
        </el-menu>

        <el-menu
            class="layouts-menu-vertical"
            :collapse-transition="false"
            :unique-opened="config.layout.menuUniqueOpened"
            :default-active="state.defaultActive"
            :collapse="true"
            ref="layoutMenuRef"
        >
            <div class="menu-bottom-item">
                <el-avatar :size="44" :src="fullUrl(adminInfo.avatar)"></el-avatar>
                <div class="menu-bottom-item-box" v-if="!config.layout.menuCollapse">
                    <div class="box-name">{{ adminInfo.nickname }}</div>
                    <div class="box-company">{{ adminInfo.email }}</div>
                </div>
            </div>
            <MenuTree :menus="settingMenu" />
            <!-- <el-menu-item @click="handleMenuBottomItemClick(1)" index="1">
                <img src="/src/assets/menu/menu9.png" alt="" style="width: 20px; height: 20px; margin-right: 10px" />
                <span>收起</span>
            </el-menu-item> -->
        </el-menu>
    </el-scrollbar>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive } from 'vue'
import { onBeforeRouteUpdate, useRoute, useRouter, type RouteLocationNormalizedLoaded } from 'vue-router'
import MenuTree from '/@/layouts/backend/components/menus/menuTree.vue'
import { useConfig } from '/@/stores/config'
import { useNavTabs } from '/@/stores/navTabs'
import { layoutMenuRef, layoutMenuScrollbarRef } from '/@/stores/refs'
import { getMenuKey } from '/@/utils/router'
import { closeShade } from '/@/utils/pageShade'
import { setNavTabsWidth } from '/@/utils/layout'
import { BEFORE_RESIZE_LAYOUT } from '/@/stores/constant/cacheKey'
import { Session } from '/@/utils/storage'
import { useAdminInfo } from '/@/stores/adminInfo'
import { fullUrl } from '/@/utils/common'

const config = useConfig()
const navTabs = useNavTabs()
const route = useRoute()
const router = useRouter()

const adminInfo = useAdminInfo()

const state = reactive({
    defaultActive: '',
})

const verticalMenusScrollbarHeight = computed(() => {
    let menuTopBarHeight = 0
    if (config.layout.menuShowTopBar) {
        menuTopBarHeight = 50
    }
    if (config.layout.layoutMode == 'Default') {
        return 'calc(100vh - ' + (32 + menuTopBarHeight) + 'px)'
    } else {
        return 'calc(100vh - ' + menuTopBarHeight + 'px)'
    }
})

// const menuText = ['数据看板', '飞行空间', '项目管理', '设备管理', '飞行记录', '应用中心', '视觉算法中心']
const menuText = ['数据看板', '飞行空间', '设备管理', '飞行记录', '应用中心', '视觉算法中心']
const menuImage = [
    '/img/menu/menu1.png',
    '/img/menu/menu2.png',
    // '/src/assets/menu/menu3.png',
    '/img/menu/menu4.png',
    '/img/menu/menu5.png',
    '/img/menu/menu6.png',
    '/img/menu/menu7.png',
    '/img/menu/menu8.png',
]

const menuList = computed(() => {
    let list: any[] = []
    navTabs.state.tabsViewRoutes.forEach((item: any) => {
        if (menuText.includes(item.meta?.title as string)) {
            list.push({
                ...item,
                meta: {
                    ...item.meta,
                    image: menuImage[menuText.indexOf(item.meta?.title as string)],
                },
            })
        }
    })
    // 按照menuText的顺序排序
    list.sort((a, b) => {
        return menuText.indexOf(a.meta?.title as string) - menuText.indexOf(b.meta?.title as string)
    })
    return list
})

const settingMenu = computed(() => {
    let settingMenu = navTabs.state.tabsViewRoutes.filter((item: any) => item.meta?.title === '设置')
    if (settingMenu.length > 0) {
        settingMenu[0].meta!.image = '/img/menu/menu8.png'
    }
    return settingMenu
})

/**
 * 激活当前路由对应的菜单
 */
const currentRouteActive = (currentRoute: RouteLocationNormalizedLoaded) => {
    // 以路由 fullPath 匹配的菜单优先，且 fullPath 无匹配时，回退到 path 的匹配菜单
    const tabView = navTabs.getTabsViewDataByRoute(currentRoute)
    if (tabView) {
        state.defaultActive = getMenuKey(tabView, tabView.meta!.matched as string)
    }
}

const handleMenuBottomItemClick = (index: number) => {
    if (index === 0) {
        router.push('/admin/settings')
    } else {
        onMenuCollapse()
    }
    console.log(index)
}

const onMenuCollapse = function () {
    if (config.layout.shrink && !config.layout.menuCollapse) {
        closeShade()
    }

    config.setLayout('menuCollapse', !config.layout.menuCollapse)

    Session.set(BEFORE_RESIZE_LAYOUT, {
        layoutMode: config.layout.layoutMode,
        menuCollapse: config.layout.menuCollapse,
    })

    // 等待侧边栏动画结束后重新计算导航栏宽度
    setTimeout(() => {
        setNavTabsWidth()
    }, 350)
}

/**
 * 滚动条滚动到激活菜单所在位置
 */
const verticalMenusScroll = () => {
    setTimeout(() => {
        let activeMenu: HTMLElement | null = document.querySelector('.el-menu.layouts-menu-vertical li.is-active')
        if (activeMenu) {
            layoutMenuScrollbarRef.value?.setScrollTop(activeMenu.offsetTop)
        }
    }, 500)
}

onMounted(() => {
    currentRouteActive(route)
    verticalMenusScroll()
    console.log('路由', navTabs.state.tabsViewRoutes)
})

onBeforeRouteUpdate((to) => {
    currentRouteActive(to)
})
</script>
<style>
.vertical-menus-scrollbar {
    height: auto;
    background-color: v-bind('config.getColorVal("menuBackground")');
    height: 100%;
    padding-bottom: 30px;
}
.el-scrollbar__view {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.layouts-menu-vertical {
    border: 0;
    padding-bottom: 30px;
    --el-menu-bg-color: v-bind('config.getColorVal("menuBackground")');
    --el-menu-text-color: v-bind('config.getColorVal("menuColor")');
    --el-menu-active-color: v-bind('config.getColorVal("menuActiveColor")');
}

.menu-bottom-item-box {
    flex: 1;
    overflow: hidden;

    span {
        line-height: normal;
    }
}

.box-name {
    font-size: 14px;
    font-weight: 600;
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.box-company {
    width: 100%;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.menu-bottom-item {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    gap: 10px;
}
</style>
