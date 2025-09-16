<template>
    <div class="video-player-container" ref="videoPlayerContainer" v-if="isShowDroneLive" v-draggable="{ isFullScreen }">
        <div class="video-player-content">
            <div class="video-player" ref="videoContainerDrone"></div>

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
                            <el-icon style="cursor: pointer" size="20" @click.stop="isShowDroneLive = false"><SwitchButton /></el-icon>
                        </el-tooltip>
                    </div>
                    <!-- 按钮操作 -->
                    <div class="video-btn">
                        <el-button type="primary" @click="handleReturn">一键返航</el-button>
                        <el-button type="success" @click="handleCancelReturn">取消返航</el-button>
                        <el-button type="info" @click="handlePause">航线暂停</el-button>
                        <el-button type="warning" @click="handleResume">航线恢复</el-button>
                    </div>
                    <!-- 变焦操作 -->
                    <div class="video-zoom">
                        <!-- 变焦的倍率 -->

                        <!-- 滑动变焦 -->
                        <!-- <el-steps direction="vertical" :active="1">
                            <el-step title="1" />
                            <el-step title="2" />
                            <el-step title="3" />
                            <el-step title="4" />
                            <el-step title="5" />
                            <el-step title="6" />
                        </el-steps> -->
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

const mqttStore = useMqttStore()

const { deviceData } = storeToRefs(mqttStore)

const mapStore = useMapStore()
const { isShowDroneLive } = storeToRefs(mapStore)

watch(isShowDroneLive, (newVal: boolean) => {
    if (newVal) {
        init()
        console.log(deviceData.value)
    } else {
        stopLive()
        liveService.destroy()
    }
})

const width = ref('600px')
const height = ref('500px')

const left = ref('0')
const top = ref('0')

const videoPlayerContainer = ref<HTMLDivElement>()

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

const deviceName = ref('飞行器')
const streamStatus = ref('等待连接...')

const init = async () => {
    // liveService.init()
    setupEventListeners()
    startLive()
}

// 开始直播
const startLive = async () => {
    console.log(disposition.djiDock.gateway_sn)
    await liveService.startLive('drone')
}

// 停止直播
const stopLive = async () => {
    await liveService.stopLive('drone')
}
const videoContainerDrone = ref<HTMLDivElement>()

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
        if (videoContainerDrone.value && data.track) {
            data.track.play(videoContainerDrone.value)
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

const handleReturn = () => {
    console.log('一键返航')
    DJIoperations.returnHome(disposition.djiDock.gateway_sn)
}

const handleCancelReturn = () => {
    console.log('取消返航')
    DJIoperations.cancelReturnHome(disposition.djiDock.gateway_sn)
}

const handlePause = () => {
    console.log('航线暂停')
    DJIoperations.pauseMission(disposition.djiDock.gateway_sn)
}

const handleResume = () => {
    console.log('航线恢复')
    DJIoperations.resumeMission(disposition.djiDock.gateway_sn)
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
        // .video-info {
        //     bottom: 0;
        //     opacity: 1;
        // }
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
        // bottom: -60px;
        bottom: 0;
        display: flex;
        justify-content: space-between;
        align-content: center;
        color: #fff;
        font-size: 12px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
        line-height: 60px;
        transition: all 0.5s ease-in-out;
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 0 0 12px 12px;
        box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.5);

        .video-options {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .video-btn {
            display: flex;
            align-items: center;
        }
    }
}
</style>
