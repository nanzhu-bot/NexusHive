import type { Plugin, ViteDevServer } from 'vite'
import { reactive } from 'vue'

interface HotUpdateState {
    // 热更新状态
    switch: boolean
    // 热更新关闭类型:terminal=WEB终端执行命令,crud=CRUD,modules=模块安装服务,config=修改系统配置
    closeType: string
    // 是否有脏文件（热更新 switch 为 false，又触发了热更新就会产生脏文件）
    dirtyFile: boolean
    // 监听是否有脏文件
    listenDirtyFileSwitch: boolean
}

/**
 * 调试模式下的 Vite 热更新相关状态（这些状态均由 Vite 服务器记录并随时同步至客户端）
 */
export const hotUpdateState = reactive<HotUpdateState>({
    switch: true,
    closeType: '',
    dirtyFile: false,
    listenDirtyFileSwitch: true,
})

/**
 * Vite 相关初始化
 */
export function init() {
    if (import.meta.hot) {
        // 监听 Vite 服务器通知热更新相关状态更新
        import.meta.hot.on('custom:change-hot-update-state', (state: Partial<HotUpdateState>) => {
            hotUpdateState.switch = state.switch ?? hotUpdateState.switch
            hotUpdateState.closeType = state.closeType ?? hotUpdateState.closeType
            hotUpdateState.dirtyFile = state.dirtyFile ?? hotUpdateState.dirtyFile
            hotUpdateState.listenDirtyFileSwitch = state.listenDirtyFileSwitch ?? hotUpdateState.listenDirtyFileSwitch
        })

        // 保持脏文件监听功能开启（同时可以从服务端同步一次热更新服务的状态数据）
        changeListenDirtyFileSwitch(true)
    }
}

/**
 * 是否在开发环境
 */
export function isDev(mode: string): boolean {
    return mode === 'development'
}

/**
 * 是否在生产环境
 */
export function isProd(mode: string | undefined): boolean {
    return mode === 'production'
}

/**
 * 调试模式下关闭热更新
 */
export const closeHotUpdate = (type: string) => {
    if (import.meta.hot) {
        import.meta.hot.send('custom:close-hot', { type })
    }
}

/**
 * 调试模式下开启热更新
 */
export const openHotUpdate = (type: string) => {
    if (import.meta.hot) {
        import.meta.hot.send('custom:open-hot', { type })
    }
}

/**
 * 调试模式下重启服务并刷新网页
 */
export const reloadServer = (type: string) => {
    if (import.meta.hot) {
        import.meta.hot.send('custom:reload-server', { type })
    }
}

/**
 * 改变脏文件监听功能的开关
 */
export const changeListenDirtyFileSwitch = (status: boolean) => {
    if (import.meta.hot) {
        import.meta.hot.send('custom:change-listen-dirty-file-switch', status)
    }
}

/**
 * 自定义热更新/热替换处理的 Vite 插件
 */
export const customHotUpdate = (): Plugin => {
    type Listeners = ((...args: any[]) => void)[]

    let addFunctionBack: Listeners = []
    let unlinkFunctionBack: Listeners = []

    // 本服务端的热更新状态数据
    const hotUpdateState: HotUpdateState = {
        switch: true,
        closeType: '',
        dirtyFile: false,
        listenDirtyFileSwitch: true,
    }

    /**
     * 同步所有热更新状态数据至客户端
     */
    const syncHotUpdateState = (server: ViteDevServer) => {
        server.ws.send('custom:change-hot-update-state', hotUpdateState)
    }

    return {
        name: 'vite-plugin-custom-hot-update',
        apply: 'serve',
        configureServer(server) {
            // 关闭热更新
            server.ws.on('custom:close-hot', ({ type }) => {
                hotUpdateState.switch = false
                hotUpdateState.closeType = type

                // 备份文件添加和删除时的函数列表
                addFunctionBack = server.watcher.listeners('add') as Listeners
                unlinkFunctionBack = server.watcher.listeners('unlink') as Listeners

                // 关闭文件添加和删除的监听
                server.watcher.removeAllListeners('add')
                server.watcher.removeAllListeners('unlink')

                syncHotUpdateState(server)

                // 文件添加时通知客户端新增了脏文件（文件删除无需记录为脏文件）
                server.watcher.on('add', () => {
                    if (hotUpdateState.listenDirtyFileSwitch) {
                        hotUpdateState.dirtyFile = true
                        syncHotUpdateState(server)
                    }
                })
            })

            // 开启热更新
            server.ws.on('custom:open-hot', () => {
                hotUpdateState.switch = true
                server.watcher.removeAllListeners('add')
                server.watcher.removeAllListeners('unlink')

                // 恢复备份的函数列表
                for (const key in addFunctionBack) {
                    server.watcher.on('add', addFunctionBack[key])
                }
                for (const key in unlinkFunctionBack) {
                    server.watcher.on('unlink', unlinkFunctionBack[key])
                }

                syncHotUpdateState(server)
            })

            // 重启热更新
            server.ws.on('custom:reload-server', () => {
                server.restart()
            })

            // 客户端可从本服务端获取热更新服务状态数据
            server.ws.on('custom:get-hot-update-state', () => {
                syncHotUpdateState(server)
            })

            // 修改监听脏文件的开关
            server.ws.on('custom:change-listen-dirty-file-switch', (status: boolean) => {
                hotUpdateState.listenDirtyFileSwitch = status
                syncHotUpdateState(server)
            })
        },
        handleHotUpdate() {
            if (!hotUpdateState.switch) {
                return []
            }
        },
    }
}
