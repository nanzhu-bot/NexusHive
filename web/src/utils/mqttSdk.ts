import { useMqttStore } from '/@/stores/mqtt'

const mqttSdk = useMqttStore()

function generateUUID() {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
        const r = (Math.random() * 16) | 0
        const v = c === 'x' ? r : (r & 0x3) | 0x8
        return v.toString(16)
    })
}

const bid = generateUUID()

export const DJIoperations = {
    // 打开服务
    sendServices: (sn: string, method: string, data: any = null) => {
        console.log('sendServices', { method, data, timestamp: Date.now(), tid: generateUUID(), bid })
        return mqttSdk.publish(`thing/product/${sn}/services`, JSON.stringify({ method, data, timestamp: Date.now(), tid: generateUUID(), bid }))
    },
    // 服务进度
    eventsReply: (sn: string, method: string, data: any = null) => {
        return mqttSdk.publish(`thing/product/${sn}/events_reply`, JSON.stringify({ method, data, timestamp: Date.now(), tid: generateUUID(), bid }))
    },
    // 订阅设备拓扑更新
    statusUpdate: (sn: string) => {
        return mqttSdk.subscribe(`sys/product/${sn}/status`)
    },
    // 关闭订阅设备拓扑更新
    statusUpdateClose: (sn: string) => {
        return mqttSdk.unsubscribe(`sys/product/${sn}/status`)
    },
    // 获取设备拓扑消息
    statusMessage: (sn: string) => {
        return mqttSdk.getMessagesByTopic(`sys/product/${sn}/status`)
    },
    // 订阅设备属性
    osd: (sn: string) => {
        return mqttSdk.subscribe(`thing/product/${sn}/osd`)
    },
    // 关闭订阅设备属性
    osdClose: (sn: string) => {
        return mqttSdk.unsubscribe(`thing/product/${sn}/osd`)
    },
    // 订阅设备状态
    deviceStatus: (sn: string) => {
        return mqttSdk.subscribe(`thing/product/${sn}/state`)
    },
    // 关闭订阅设备状态
    deviceStatusClose: (sn: string) => {
        return mqttSdk.unsubscribe(`thing/product/${sn}/state`)
    },
    // 订阅设备服务回复
    deviceServicesReply: (sn: string) => {
        return mqttSdk.subscribe(`thing/product/${sn}/services_reply`)
    },
    // 关闭订阅设备服务回复
    deviceServicesReplyClose: (sn: string) => {
        return mqttSdk.unsubscribe(`thing/product/${sn}/services_reply`)
    },
    // 订阅进度回复
    deviceEvents: (sn: string) => {
        return mqttSdk.subscribe(`thing/product/${sn}/events`)
    },
    // 关闭订阅进度回复
    deviceEventsClose: (sn: string) => {
        return mqttSdk.unsubscribe(`thing/product/${sn}/events`)
    },
    // 终止任务
    terminateMission: (sn: string, data: any = {}) => {
        return mqttSdk.publish(
            `thing/product/${sn}/services`,
            JSON.stringify({ method: 'flighttask_stop', data, timestamp: Date.now(), tid: generateUUID(), bid })
        )
    },
    // 一键返航
    returnHome: (sn: string, data: any = {}) => {
        return mqttSdk.publish(
            `thing/product/${sn}/services`,
            JSON.stringify({ method: 'return_home', data, timestamp: Date.now(), tid: generateUUID(), bid })
        )
    },
    // 取消返航
    cancelReturnHome: (sn: string, data: any = {}) => {
        return mqttSdk.publish(
            `thing/product/${sn}/services`,
            JSON.stringify({ method: 'return_home_cancel', data, timestamp: Date.now(), tid: generateUUID(), bid })
        )
    },
    // 航线暂停
    pauseMission: (sn: string, data: any = {}) => {
        return mqttSdk.publish(
            `thing/product/${sn}/services`,
            JSON.stringify({ method: 'flighttask_pause', data, timestamp: Date.now(), tid: generateUUID(), bid })
        )
    },
    // 航线恢复
    resumeMission: (sn: string, data: any = {}) => {
        return mqttSdk.publish(
            `thing/product/${sn}/services`,
            JSON.stringify({ method: 'flighttask_recovery', data, timestamp: Date.now(), tid: generateUUID(), bid })
        )
    },
}
