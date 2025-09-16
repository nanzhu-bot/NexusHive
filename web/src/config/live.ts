// 直播配置文件
export interface LiveConfig {
    // 声网配置
    agora: {
        appId: string
        channel: string
        token: string
        uid: number
    }

    // 飞行器配置
    drone: {
        appId: string
        channel: string
        token: string
        uid: number
    }

    // DJI Dock配置
    djiDock: {
        gateway_sn: string
        topics: {
            services: string
            services_reply: string
        }
        videoId: string
    }

    // MQTT配置
    mqtt: {
        broker: string
        username: string
        password: string
    }
}

// 重试机制配置
export interface RetryConfig {
    maxRetries: number
    retryDelay: number
    maxWaitTime: number
    enableRetry: boolean
}

// 默认重试配置
export const defaultRetryConfig: RetryConfig = {
    maxRetries: 3, // 最大重试次数
    retryDelay: 2000, // 重试间隔（毫秒）
    maxWaitTime: 10000, // 最大等待设备就绪时间（毫秒）
    enableRetry: true, // 是否启用重试机制
}

// 获取重试配置
export const getRetryConfig = (): RetryConfig => {
    // 可以从环境变量或本地存储中读取配置
    return {
        ...defaultRetryConfig,
        maxRetries: parseInt(import.meta.env.VITE_LIVE_MAX_RETRIES || '3'),
        retryDelay: parseInt(import.meta.env.VITE_LIVE_RETRY_DELAY || '2000'),
        maxWaitTime: parseInt(import.meta.env.VITE_LIVE_MAX_WAIT_TIME || '10000'),
        enableRetry: import.meta.env.VITE_LIVE_ENABLE_RETRY !== 'false',
    }
}

// 无人机sn 1581F6QAD247P00GJZWY 176-0-0
// 机场sn 7CTXN3S00B08GE 165-0-7

// 默认配置
export const defaultLiveConfig: LiveConfig = {
    agora: {
        appId: '0fa99f43d6ba4c6fb22c0fde4f27e596',
        channel: 'feixingqi',
        token: '007eJxSYLgnleuyw7r3ZfKcLekyZz23pDNaL19jfGWfwvT7h/RM3wgpMJiYmRmbGyWmGFgkJpmYWCRZGCcbmVomJ5sYJRoYJhqbdRdtyRDgY2BQ3ruSiZGBkYGFgZEBxGcCk8xgkgVMsjNkZSZnJOalMzAAAgAA//93Px9A',
        uid: 123456,
    },

    drone: {
        appId: '0fa99f43d6ba4c6fb22c0fde4f27e596',
        channel: 'feixingqi',
        token: '007eJxTYLgnleuyw7r3ZfKcLekyZz23pDNaL19jfGWfwvT7h/RM3wgpMJiYmRmbGyWmGFgkJpmYWCRZGCcbmVomJ5sYJRoYJhqbdRdtyRDgY2BQ3ruSiZGBkYGFgZEBxGcCk8xgkgVMsjNkZSZnJOalMzAAAgAA//93Px9A',
        uid: 123456,
    },

    djiDock: {
        gateway_sn: '7CTXN3S00B08GE',
        topics: {
            services: 'thing/product/7CTXN3S00B08GE/services',
            services_reply: 'thing/product/7CTXN3S00B08GE/services_reply',
        },
        videoId: '7CTXN3S00B08GE/165-0-7/normal-0',
    },

    mqtt: {
        broker: 'ws://121.5.46.95:8083/mqtt',
        username: 'pilot',
        password: 'pilot123',
    },
}

// 获取配置
export const getLiveConfig = (): LiveConfig => {
    // 可以从环境变量或本地存储中读取配置
    return defaultLiveConfig
}

// JavaScript 生成示例
function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        const r = (Math.random() * 16) | 0
        const v = c === 'x' ? r : (r & 0x3) | 0x8
        return v.toString(16)
    })
}

const bid = generateUUID()

// DJI Dock服务命令
export const djiDockCommands = {
    // 开始直播
    startLive: () => {
        return {
            bid: bid,
            method: 'live_start_push',
            data: {
                video_id: defaultLiveConfig.djiDock.videoId,
                url_type: 0,
                url: `channel=${defaultLiveConfig.agora.channel}&sn=${defaultLiveConfig.djiDock.gateway_sn}&token=${encodeURIComponent(defaultLiveConfig.agora.token)}&uid=${defaultLiveConfig.agora.uid}`,
                video_quality: 3,
            },
            timestamp: Date.now(),
            tid: generateUUID(),
        }
    },

    startLive1: () => {
        return {
            bid: bid,
            method: 'live_start_push',
            data: {
                video_id: defaultLiveConfig.drone.videoId,
                url_type: 0,
                url: `channel=${defaultLiveConfig.agora.channel}&sn=1581F6QAD247P00GJZWY&token=${encodeURIComponent(defaultLiveConfig.agora.token)}&uid=${defaultLiveConfig.agora.uid}`,
                video_quality: 3,
            },
            timestamp: Date.now(),
            tid: generateUUID(),
        }
    },

    live_camera_change: () => {
        return {
            bid: bid,
            method: 'live_camera_change',
            data: {
                video_id: defaultLiveConfig.djiDock.videoId,
                camera_position: 1,
            },
            timestamp: Date.now(),
            tid: generateUUID(),
        }
    },

    // 停止直播
    stopLive: () => {
        return {
            bid: bid,
            method: 'live_stop_push',
            data: {
                video_id: '7CTXN3S00B08GE/165-0-7/normal-0',
            },
            timestamp: Date.now(),
            tid: generateUUID(),
        }
    },

    getStatus: {
        service: 'getStatus',
    },

    restart: {
        service: 'restart',
    },
}

// 声网事件处理
export const agoraEvents = {
    // 连接状态变化
    connectionStateChange: 'connection-state-change',

    // 用户发布/取消发布
    userPublished: 'user-published',
    userUnpublished: 'user-unpublished',

    // 网络质量
    networkQuality: 'network-quality',

    // 异常事件
    exception: 'exception',
}

// 直播状态枚举
export enum LiveStatus {
    IDLE = 'idle',
    STARTING = 'starting',
    LIVE = 'live',
    STOPPING = 'stopping',
    ERROR = 'error',
}

// 设备状态枚举
export enum DeviceStatus {
    ONLINE = 'online',
    OFFLINE = 'offline',
    ERROR = 'error',
    MAINTENANCE = 'maintenance',
}
