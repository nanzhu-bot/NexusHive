<template>
    <div class="drone-live">
        <div class="live-container">
            <!-- 视频播放器 -->
            <div class="video-player" ref="videoPlayerRef">
                <div class="video-content">
                    <div ref="videoContainer" class="video-container"></div>
                    <div class="video-overlay">
                        <div class="live-indicator">
                            <span class="live-dot"></span>
                            <span class="live-text">LIVE</span>
                        </div>
                        <div class="video-info">
                            <span class="device-name">{{ deviceName }}</span>
                            <span class="stream-status">{{ streamStatus }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 控制面板 -->
            <div class="control-panel">
                <div class="control-header">
                    <h3>直播控制</h3>
                    <div class="connection-status" :class="{ connected: isConnected }">
                        {{ isConnected ? '已连接' : '未连接' }}
                    </div>
                </div>

                <div class="control-buttons">
                    <button class="control-btn start-btn" @click="startLive">
                        <span class="btn-icon">▶️</span>
                        <span>开始直播</span>
                    </button>

                    <button class="control-btn stop-btn" @click="stopLive">
                        <span class="btn-icon">⏹️</span>
                        <span>停止直播</span>
                    </button>
                </div>

                <div class="stream-info">
                    <div class="info-item" @click="handleCameraSwitch">
                        <span class="info-label">直播相机切换:</span>
                        <span class="info-value" :class="liveStatusClass">{{ liveStatus }}</span>
                    </div>
                </div>

                <div class="mqtt-log">
                    <div class="log-header">
                        <span>MQTT 日志</span>
                        <button class="clear-btn" @click="clearLog">清空</button>
                    </div>
                    <div class="log-content" ref="logContainer">
                        <div v-for="(log, index) in mqttLogs" :key="index" class="log-item">
                            <span class="log-time">{{ log.time }}</span>
                            <span class="log-topic">{{ log.topic }}</span>
                            <span class="log-message">{{ log.message }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick, computed, watch } from 'vue'
import { liveService } from '/@/services/liveService'
import { LiveStatus, djiDockCommands } from '/@/config/live'
import { useMqttStore } from '/@/stores/mqtt'
import { DJIoperations } from '/@/utils/mqttSdk'
import { defaultLiveConfig } from '/@/config/live'
import { disposition } from '/@/config/disposition'
import { ElMessage } from 'element-plus'

// 响应式数据
const videoPlayerRef = ref<HTMLElement>()
const videoContainer = ref<HTMLElement>()
const logContainer = ref<HTMLElement>()

const isConnected = ref(false)
const isLiveStarted = ref(false)
const deviceName = ref('Dock 2')
const liveStatus = ref('未开始')
const streamStatus = ref('等待连接...')
const connectionQuality = ref('未知')

const mqttLogs = ref<Array<{ time: string; topic: string; message: string }>>([])

const mqttStore = useMqttStore()

// 计算属性

const liveStatusClass = computed(() => {
    return {
        'status-live': liveStatus.value === '直播中',
        'status-stopped': liveStatus.value === '已停止',
        'status-error': liveStatus.value === '错误',
    }
})

const mqttMessages = computed(() => {
    return mqttStore.messages
})

watch(mqttMessages, (newVal: any) => {
    console.log('mqttMessages', JSON.parse(newVal.payload))
})

// 添加日志
const addLog = (topic: string, message: string) => {
    const time = new Date().toLocaleTimeString()
    mqttLogs.value.push({ time, topic, message })

    // 限制日志数量
    if (mqttLogs.value.length > 50) {
        mqttLogs.value.shift()
    }

    // 滚动到底部
    nextTick(() => {
        if (logContainer.value) {
            logContainer.value.scrollTop = logContainer.value.scrollHeight
        }
    })
}

// 清空日志
const clearLog = () => {
    mqttLogs.value = []
}

// 开始直播
const startLive = async () => {
    try {
        await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_start_push', disposition.getDeviceData())
        // await mqttStore.publish(defaultLiveConfig.djiDock.topics.services, JSON.stringify(djiDockCommands.startLive()))
        await liveService.startLive('drone')
    } catch (error) {
        console.error('开始直播失败:', error)
        addLog('Error', `开始直播失败: ${error}`)
    }
}

// 停止直播
const stopLive = async () => {
    // 发送MQTT命令停止直播
    // await mqttStore.publish(defaultLiveConfig.djiDock.topics.services, JSON.stringify(djiDockCommands.stopLive()))
    await DJIoperations.sendServices(disposition.djiDock.gateway_sn, 'live_stop_push', { video_id: disposition.device.videoId })
    mqttStore.unsubscribe(defaultLiveConfig.djiDock.topics.services_reply)
    try {
        await liveService.stopLive('drone')
    } catch (error) {
        console.error('停止直播失败:', error)
        addLog('Error', `停止直播失败: ${error}`)
    }
}

const handleCameraSwitch = () => {
    liveService.switchCamera()
}

// 设置事件监听
const setupEventListeners = () => {
    // 声网事件
    liveService.on('agora:initialized', (data) => {
        addLog('Agora', data.success ? '初始化成功' : `初始化失败: ${data.error}`)
    })

    liveService.on('agora:joined', (data) => {
        addLog('Agora', `已加入频道: ${data.channel}`)
        isConnected.value = true
    })

    liveService.on('agora:left', (data) => {
        addLog('Agora', `已离开频道: ${data.channel}`)
        isConnected.value = false
    })

    liveService.on('agora:videoTrack', (data) => {
        console.log('agora:videoTrack', '111111111111111111111111111111111111111111111')
        if (videoContainer.value && data.track) {
            data.track.play(videoContainer.value)
            streamStatus.value = '视频流已连接'
            addLog('Agora', '视频流已订阅')
        }
    })

    liveService.on('agora:connectionState', (data) => {
        addLog('Agora', `连接状态: ${data.previous} -> ${data.current}`)
    })

    // MQTT事件
    liveService.on('mqtt:subscribed', (data) => {
        addLog('MQTT', `已订阅主题: ${data.topics?.join(', ')}`)
    })

    liveService.on('mqtt:command', (data) => {
        addLog('MQTT', `发送命令: ${data.command}`)
    })

    liveService.on('mqtt:message', (data) => {
        addLog('MQTT', `${data.topic}: ${JSON.stringify(data.data)}`)
    })

    // 直播状态事件
    liveService.on('live:status', (data) => {
        // 处理设备等待状态
        if (data.status === 'waiting_mqtt') {
            liveStatus.value = '等待MQTT连接...'
            addLog('System', data.message || '等待MQTT连接...')
            return
        } else if (data.status === 'waiting_device') {
            liveStatus.value = '等待设备状态同步...'
            addLog('System', data.message || '等待设备状态同步...')
            return
        } else if (data.status === 'waiting_data') {
            liveStatus.value = '等待设备数据同步...'
            addLog('System', data.message || '等待设备数据同步...')
            return
        } else if (data.status === 'device_offline') {
            liveStatus.value = '设备离线'
            addLog('System', data.message || '设备可能离线，请检查设备状态')
            return
        }

        // 处理正常直播状态
        liveStatus.value =
            data.status === LiveStatus.LIVE
                ? '直播中'
                : data.status === LiveStatus.IDLE
                  ? '未开始'
                  : data.status === LiveStatus.STARTING
                    ? '启动中...'
                    : data.status === LiveStatus.STOPPING
                      ? '停止中...'
                      : '错误'

        isLiveStarted.value = data.status === LiveStatus.LIVE
        addLog('Live', `状态变更: ${liveStatus.value}`)
    })

    // 重试机制事件
    liveService.on('live:retry_attempt', (data) => {
        const retryMessage = `第${data.retryCount}/${data.maxRetries}次重试，${data.nextRetryIn}ms后重试`
        addLog('Retry', retryMessage)
        liveStatus.value = `重试中 (${data.retryCount}/${data.maxRetries})`

        // 显示重试进度
        ElMessage.warning({
            message: retryMessage,
            duration: data.nextRetryIn,
        })
    })

    liveService.on('live:retry_success', (data) => {
        const successMessage = `重试成功，共重试${data.retryCount}次`
        addLog('Retry', successMessage)
        ElMessage.success(successMessage)
    })

    // 设备状态事件（已简化，暂时移除）

    // 错误事件
    liveService.on('live:error', (data) => {
        let errorMessage = `${data.operation} 失败: ${data.error}`

        // 如果是重试相关的错误，显示重试信息
        if (data.retryCount !== undefined) {
            errorMessage += ` (已重试${data.retryCount}/${data.maxRetries}次)`
        }

        addLog('Error', errorMessage)

        // 特殊处理513010错误
        if (data.error && data.error.toString().includes('513010')) {
            ElMessage.error('设备网络连接异常，请检查设备网络状态后重试')
        } else {
            ElMessage.error(errorMessage)
        }
    })

    liveService.on('agora:error', (data) => {
        addLog('Agora Error', `${data.operation} 失败: ${data.error}`)
    })

    liveService.on('mqtt:error', (data) => {
        addLog('MQTT Error', `${data.topic} 错误: ${data.error}`)
    })
}

// 生命周期
onMounted(async () => {
    liveService.init()
    setupEventListeners()
})

onUnmounted(() => {
    liveService.destroy()
    addLog('System', '组件已销毁')
})
</script>

<style scoped lang="scss">
.drone-live {
    width: 100%;
    height: 100%;
    background-color: #f5f5f5;
    padding: 20px;

    .live-container {
        display: flex;
        gap: 20px;
        height: 100%;

        .video-player {
            flex: 1;
            background-color: #000;
            border-radius: 12px;
            overflow: hidden;
            position: relative;

            .video-placeholder {
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #1a1a1a;

                .placeholder-content {
                    text-align: center;
                    color: #fff;

                    .placeholder-icon {
                        font-size: 48px;
                        margin-bottom: 16px;
                    }

                    .placeholder-text {
                        font-size: 16px;
                        opacity: 0.7;
                    }
                }
            }

            .video-content {
                width: 100%;
                height: 100%;
                position: relative;

                .video-container {
                    width: 100%;
                    height: 100%;
                }

                .video-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    pointer-events: none;
                    padding: 20px;

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

                    .video-info {
                        position: absolute;
                        bottom: 20px;
                        left: 20px;
                        right: 20px;
                        display: flex;
                        justify-content: space-between;
                        color: #fff;
                        font-size: 14px;
                        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
                    }
                }
            }
        }

        .control-panel {
            width: 320px;
            background-color: #fff;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;

            .control-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding-bottom: 16px;
                border-bottom: 1px solid #e5e5e5;

                h3 {
                    margin: 0;
                    font-size: 18px;
                    font-weight: 600;
                }

                .connection-status {
                    padding: 4px 12px;
                    border-radius: 12px;
                    font-size: 12px;
                    background-color: #ff4757;
                    color: #fff;

                    &.connected {
                        background-color: #2ed573;
                    }
                }
            }

            .control-buttons {
                display: flex;
                flex-direction: column;
                gap: 12px;

                .control-btn {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    padding: 12px 20px;
                    border: none;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: all 0.2s ease;

                    &:disabled {
                        opacity: 0.5;
                        cursor: not-allowed;
                    }

                    &.start-btn {
                        background-color: #2ed573;
                        color: #fff;

                        &:hover:not(:disabled) {
                            background-color: #26d0a8;
                        }
                    }

                    &.stop-btn {
                        background-color: #ff4757;
                        color: #fff;

                        &:hover:not(:disabled) {
                            background-color: #ff3742;
                        }
                    }

                    .btn-icon {
                        font-size: 16px;
                    }
                }
            }

            .stream-info {
                display: flex;
                flex-direction: column;
                gap: 8px;

                .info-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 0;

                    .info-label {
                        font-size: 14px;
                        color: #666;
                    }

                    .info-value {
                        font-size: 14px;
                        font-weight: 500;

                        &.status-online {
                            color: #2ed573;
                        }

                        &.status-offline {
                            color: #ff4757;
                        }

                        &.status-error {
                            color: #ffa502;
                        }

                        &.status-live {
                            color: #2ed573;
                        }

                        &.status-stopped {
                            color: #747d8c;
                        }
                    }
                }
            }

            .mqtt-log {
                flex: 1;
                display: flex;
                flex-direction: column;
                border: 1px solid #e5e5e5;
                border-radius: 8px;
                overflow: hidden;

                .log-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 12px 16px;
                    background-color: #f8f9fa;
                    border-bottom: 1px solid #e5e5e5;
                    font-size: 14px;
                    font-weight: 500;

                    .clear-btn {
                        padding: 4px 8px;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                        background-color: #fff;
                        font-size: 12px;
                        cursor: pointer;

                        &:hover {
                            background-color: #f8f9fa;
                        }
                    }
                }

                .log-content {
                    flex: 1;
                    padding: 12px;
                    overflow-y: auto;
                    max-height: 200px;
                    background-color: #fafafa;

                    .log-item {
                        display: flex;
                        flex-direction: column;
                        gap: 4px;
                        padding: 8px;
                        margin-bottom: 8px;
                        background-color: #fff;
                        border-radius: 4px;
                        border-left: 3px solid #007bff;
                        font-size: 12px;

                        .log-time {
                            color: #666;
                            font-weight: 500;
                        }

                        .log-topic {
                            color: #007bff;
                            font-weight: 500;
                        }

                        .log-message {
                            color: #333;
                            word-break: break-all;
                        }
                    }
                }
            }
        }
    }
}
</style>
