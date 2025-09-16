<template>
    <el-dialog :model-value="livePop" title="机舱直播" width="800px" center @close="livePop = false" class="warning-list-dialog">
        <div class="video-player" ref="videoContainer"></div>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, inject } from 'vue'
import { liveService } from '/@/services/liveService'
import { LiveStatus } from '/@/config/live'
import { useMqttStore } from '/@/stores/mqtt'
import { disposition } from '/@/config/disposition'
import { DJIoperations } from '/@/utils/mqttSdk'

const livePop = inject<boolean>('livePop')
const mqttStore = useMqttStore()

watch(livePop, (newVal: boolean) => {
    if (newVal) {
        init()
    } else {
        stopLive()
        liveService.destroy()
    }
})

const init = async () => {
    liveService.init()
    setupEventListeners()
    startLive()
}

// 开始直播
const startLive = async () => {
    await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_start_push', disposition.getDeviceData())
    await liveService.startLive('drone')
}

// 停止直播
const stopLive = async () => {
    await liveService.stopLive('drone')
    await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_stop_push', { video_id: disposition.device.videoId })
}
const videoContainer = ref<HTMLDivElement>()

// 设置事件监听
const setupEventListeners = () => {
    // 声网事件
    liveService.on('agora:initialized', (data) => {
        console.log('Agora', data.success ? '初始化成功' : `初始化失败: ${data.error}`)
    })

    liveService.on('agora:joined', (data) => {
        console.log('Agora', `已加入频道: ${data.channel}`)
    })

    liveService.on('agora:left', (data) => {
        console.log('Agora', `已离开频道: ${data.channel}`)
    })

    liveService.on('agora:videoTrack', (data) => {
        if (videoContainer.value && data.track) {
            data.track.play(videoContainer.value)
            console.log('Agora', '视频流已订阅')
        }
    })

    liveService.on('agora:connectionState', (data) => {
        console.log('Agora', `连接状态: ${data.previous} -> ${data.current}`)
    })

    // MQTT事件
    liveService.on('mqtt:subscribed', (data) => {
        console.log('MQTT', `已订阅主题: ${data.topics?.join(', ')}`)
    })

    liveService.on('mqtt:command', (data) => {
        console.log('MQTT', `发送命令: ${data.command}`)
    })

    liveService.on('mqtt:message', (data) => {
        console.log('MQTT', `${data.topic}: ${JSON.stringify(data.data)}`)
    })

    // 直播状态事件
    liveService.on('live:status', (data) => {})

    // 设备状态事件（已简化，暂时移除）

    // 错误事件
    liveService.on('live:error', (data) => {
        console.log('Error', `${data.operation} 失败: ${data.error}`)
    })

    liveService.on('agora:error', (data) => {
        console.log('Agora Error', `${data.operation} 失败: ${data.error}`)
    })

    liveService.on('mqtt:error', (data) => {
        console.log('MQTT Error', `${data.topic} 错误: ${data.error}`)
    })
}
</script>

<style scoped lang="scss">
.warning-list-dialog {
    border-radius: 24px;
    background: #fff;

    .video-player {
        width: 100%;
        height: 500px;
    }
}
</style>
