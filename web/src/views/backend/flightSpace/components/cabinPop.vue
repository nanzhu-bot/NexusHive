<template>
    <div class="video-player-container" ref="videoPlayerContainer" v-if="isShowCabinLive" v-draggable="{ isFullScreen }">
        <div class="video-player-content">
            <div class="video-player" ref="videoContainer"></div>

            <div class="video-overlay">
                <div class="live-indicator">
                    <span class="live-dot"></span>
                    <span class="live-text">LIVE</span>
                </div>
                <span class="device-name">{{ deviceName }}</span>
                <div class="video-info">
                    <div class="video-options">
                        <el-tooltip class="box-item" effect="dark" :content="isFullScreen ? '退出全屏' : '全屏'" placement="top">
                            <el-icon style="cursor: pointer" size="20" @click.stop="isFullScreen = !isFullScreen"><FullScreen /></el-icon>
                        </el-tooltip>
                        <el-tooltip class="box-item" effect="dark" content="关闭" placement="top">
                            <el-icon style="cursor: pointer" size="20" @click.stop="isShowCabinLive = false"><SwitchButton /></el-icon>
                        </el-tooltip>
                    </div>
                    <span class="stream-status">{{ streamStatus }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, watch, inject } from 'vue'
import { liveService } from '/@/services/liveService'
import { LiveStatus } from '/@/config/live'
import { useMqttStore } from '/@/stores/mqtt'
import { disposition } from '/@/config/disposition'
import { DJIoperations } from '/@/utils/mqttSdk'
import { FullScreen, SwitchButton } from '@element-plus/icons-vue'
import { storeToRefs } from 'pinia'
import { useMapStore } from '/@/stores/map'
import { errorCode } from '/@/config/eorr'
import { ElMessage } from 'element-plus'

const mapStore = useMapStore()
const { isShowCabinLive } = storeToRefs(mapStore)

const mqttStore = useMqttStore()

const width = ref('600px')
const height = ref('500px')
// 记录全屏前的位置
const left = ref('0')
const top = ref('0')

const videoPlayerContainer = ref<HTMLDivElement>()

watch(isShowCabinLive, (newVal: boolean) => {
    if (newVal) {
        init()
    } else {
        stopLive()
        liveService.destroy()
    }
})

const message = computed(() => mqttStore.getMessagesByTopic(`thing/product/${disposition.djiDock.gateway_sn}/services_reply`))

watch(message, (newVal: any) => {
    if (newVal.payload) {
        const data = JSON.parse(newVal.payload).data
        if (data.result != 0) {
            ElMessage.error(errorCode[data.result] || '未知错误')
        }
        console.log(JSON.parse(newVal.payload).data)
    }
})

// 是否全屏
const isFullScreen = ref(false)

watch(isFullScreen, (newVal: boolean) => {
    if (newVal) {
        videoPlayerContainer.value!.style.transition = 'all 0.3s ease-in-out'
        left.value = videoPlayerContainer.value!.style.left
        top.value = videoPlayerContainer.value!.style.top
        width.value = '100%'
        height.value = '100%'
        videoPlayerContainer.value!.style.left = '0'
        videoPlayerContainer.value!.style.top = '0'
    } else {
        width.value = '600px'
        height.value = '500px'
        videoPlayerContainer.value!.style.left = left.value
        videoPlayerContainer.value!.style.top = top.value
        setTimeout(() => {
            videoPlayerContainer.value!.style.transition = ''
        }, 300)
    }
})

const deviceName = ref('机舱')
const streamStatus = ref('等待连接...')

const init = async () => {
    // liveService.init()
    setupEventListeners()
    startLive()
}

// 开始直播
const startLive = async () => {
    console.log(disposition.djiDock.gateway_sn)
    liveService.setRetryConfig(5, 3000) // 最大重试5次，每次间隔3秒
    // await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_start_push', disposition.getDjiDockData())
    await liveService.startLive('cabin')
}

// 停止直播
const stopLive = async () => {
    await liveService.stopLive('cabin')
    // await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_stop_push', { video_id: disposition.djiDock.videoId })
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
    // 视频流已订阅
    liveService.on('agora:videoTrack', (data) => {
        if (videoContainer.value && data.track) {
            data.track.play(videoContainer.value)
            streamStatus.value = '视频流已连接'
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

const handleFullScreen = () => {
    console.log('handleFullScreen')
}
</script>

<style scoped lang="scss">
.video-player-container {
    width: v-bind(width);
    height: v-bind(height);
    background: #fff;
    border-radius: 12px;
    user-select: none; /* 防止拖动时选中文字 */
    position: absolute;
    top: 0;
    left: 0;
    z-index: 100;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0px 0px 4px 0px #0000001a;

    .video-player-content {
        flex: 1;
        width: 100%;
        display: flex;
        position: relative;
    }
}

.video-player {
    flex: 1;
    width: 100%;
    background-color: #000;
}

.video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 20px;
    z-index: 99;

    &:hover {
        .video-info {
            bottom: 0;
            opacity: 1;
        }
    }

    .live-indicator {
        position: absolute;
        top: 20px;
        left: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        background-color: rgba(255, 0, 0, 0.8);
        color: #fff;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;

        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #fff;
            border-radius: 50%;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                opacity: 1;
            }
        }
    }

    .device-name {
        position: absolute;
        top: 20px;
        right: 20px;
        color: #fff;
        font-size: 14px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    .video-info {
        width: 100%;
        height: 60px;
        padding: 0 20px;
        position: absolute;
        left: 0;
        bottom: -60px;
        display: flex;
        justify-content: space-between;
        align-content: center;
        color: #fff;
        font-size: 12px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        line-height: 60px;
        transition: all 0.5s ease-in-out;
        opacity: 0;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 0 0 12px 12px;
        box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);

        .video-options {
            display: flex;
            align-items: center;
            gap: 20px;
        }
    }
}
</style>
