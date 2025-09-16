import { defineStore } from 'pinia'
import type { RouteRecordRaw } from 'vue-router'
import type { SiteConfig } from '/@/stores/interface'

export const useSiteConfig = defineStore('siteConfig', {
    state: (): SiteConfig => {
        return {
            siteName: '云上飞控',
            version: '',
            cdnUrl: '',
            apiUrl: '',
            upload: {
                mode: 'local',
            },
            headNav: [],
            recordNumber: '',
            cdnUrlParams: '',
            initialize: false,
            userInitialize: false,
        }
    },
    actions: {
        dataFill(state: SiteConfig) {
            // 使用 this.$patch(state) 时 headNav 的类型异常，直接赋值
            this.$state = state
        },
        setHeadNav(headNav: RouteRecordRaw[]) {
            this.headNav = headNav
        },
        setInitialize(initialize: boolean) {
            this.initialize = initialize
        },
        setUserInitialize(userInitialize: boolean) {
            this.userInitialize = userInitialize
        },
    },
})
