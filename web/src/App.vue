<template>
    <el-config-provider :value-on-clear="() => null" :locale="lang">
        <router-view></router-view>
    </el-config-provider>
</template>
<script setup lang="ts">
import { onMounted, watch, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useConfig } from '/@/stores/config'
import { setTitleFromRoute } from '/@/utils/common'
import iconfontInit from '/@/utils/iconfont'
import { init as viteInit } from '/@/utils/vite'
import { useMqttStore } from '/@/stores/mqtt'
import axios from 'axios'
import { disposition } from '/@/config/disposition'
import { useMedia } from '/@/stores/media'
// modules import mark, Please do not remove.
const route = useRoute()
const config = useConfig()
const mqttStore = useMqttStore()
const mediaStore = useMedia()

// 初始化 element 的语言包
const { getLocaleMessage } = useI18n()
const lang = getLocaleMessage(config.lang.defaultLang) as any
onMounted(async () => {
    viteInit()
    iconfontInit()
    getAgoraToken()
    console.log('🚀 开始初始化MQTT连接...')
    await mqttStore.initMqtt()
    console.log('✅ MQTT初始化完成')
    await getEquipmentList()
    mqttStore.getTaskPlan()
    // 页面关闭时断开MQTT连接
    window.addEventListener('beforeunload', () => {
        mqttStore.disconnect()
        mqttStore.unsubscribeDeviceOsd()
    })
    // 获取媒体库配置
    await mediaStore.getMediaConfig()
    // Modules onMounted mark, Please do not remove.
})

// 获取声网的token
const getAgoraToken = async () => {
    // 机舱token
    const response = await axios.post(`${import.meta.env.VITE_AXIOS_AGORA_URL}`, {
        channelName: disposition.cabin.channel,
        uid: disposition.cabin.uid,
        tokenExpireTs: 3600,
        privilegeExpireTs: 3600,
        serviceRtc: {
            enable: true,
            role: 1,
        },
    })
    disposition.cabin.token = response.data.data.token
    // 飞行器token
    const response1 = await axios.post(`${import.meta.env.VITE_AXIOS_AGORA_URL}`, {
        channelName: disposition.drone.channel,
        uid: disposition.drone.uid,
        tokenExpireTs: 3600,
        privilegeExpireTs: 3600,
        serviceRtc: {
            enable: true,
            role: 1,
        },
    })
    disposition.drone.token = response1.data.data.token
}

// 获取设备列表
const getEquipmentList = async () => {
    await mqttStore.getDeviceList()
}

onUnmounted(() => {
    window.removeEventListener('beforeunload', () => {
        mqttStore.disconnect()
        mqttStore.unsubscribeDeviceOsd()
    })
    // mqttStore.disconnect()
    // mqttStore.unsubscribeDeviceOsd()
})

// 监听路由变化时更新浏览器标题
watch(
    () => route.path,
    () => {
        setTitleFromRoute()
    }
)
</script>
