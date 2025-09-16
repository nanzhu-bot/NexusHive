import type { RouteRecordRaw } from 'vue-router'

/**
 * 后台基础路由路径
 * 您可以随时于后台->系统配置中修改此值，程序可自动完成代码修改，同时建立对应的API入口和禁止admin应用访问
 */
export const adminBaseRoutePath = '/admin'

/*
 * 后台基础静态路由
 */
const adminBaseRoute: RouteRecordRaw = {
    path: adminBaseRoutePath,
    name: 'admin',
    component: () => import('/@/layouts/backend/index.vue'),
    // 直接重定向到 loading 路由
    redirect: adminBaseRoutePath + '/loading',
    meta: {
        title: `pagesTitle.admin`,
    },
    children: [
        {
            path: 'loading/:to?',
            name: 'adminMainLoading',
            component: () => import('/@/layouts/common/components/loading.vue'),
            meta: {
                title: `pagesTitle.loading`,
            },
        },
        // 添加 equipment 路由
        {
            path: 'equipment',
            name: 'equipment',
            component: () => import('/@/views/backend/equipment/index.vue'),
            meta: {
                title: '设备管理',
            },
            children: [
                {
                    path: 'device',
                    name: 'equipmentDevice',
                    component: () => import('/@/views/backend/equipment/device/index.vue'),
                    meta: {
                        title: '设备管理',
                    },
                },
                {
                    path: 'aircraft',
                    name: 'equipmentAircraft',
                    component: () => import('/@/views/backend/equipment/aircraft/index.vue'),
                    meta: {
                        title: '飞行器市场',
                    },
                },
                {
                    path: 'load',
                    name: 'equipmentLoad',
                    component: () => import('/@/views/backend/equipment/load/index.vue'),
                    meta: {
                        title: '负载市场',
                    },
                },
            ],
        },
    ],
}

export default adminBaseRoute
