<template>
    <div class="config-section" v-loading="loading">
        <!-- 顶部控制区域 -->
        <div class="top-controls">
            <div class="left-controls">
                <div class="remote-debug">
                    <span class="control-label">远程调试</span>
                    <el-switch style="--el-switch-on-color: #00386d" v-model="remoteDebug" inline-prompt active-text="开" inactive-text="关" />
                </div>
            </div>

            <!-- <div class="right-controls">
                <el-button type="primary" class="flight-test-btn">机场试飞</el-button>
                <el-button class="feedback-btn">设备定规与反馈</el-button>
            </div> -->
        </div>

        <!-- 机场控制区域 -->
        <div class="control-section">
            <div class="section-header">
                <div class="section-title">
                    <span class="title-text">机场控制</span>
                    <!-- <el-button class="live-btn" @click="livePop = true">直播</el-button> -->
                </div>
            </div>

            <div class="control-grid">
                <!-- 第一行 -->
                <div class="control-row">
                    <div class="airport-system">
                        <div class="card-header">
                            <span class="card-title">机场系统</span>
                            <span class="status-text">正常工作中</span>
                        </div>
                        <div class="status-label" @click="handlePower('device_reboot')">重启</div>
                    </div>

                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">舱盖</span>
                        </div>
                        <div class="card-content">
                            <el-button
                                size="small"
                                :class="{ active: deviceData.cover_state == 1 }"
                                class="toggle-btn"
                                @click="handlePower('cover_open')"
                                >开</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.cover_state == 0 }"
                                class="toggle-btn"
                                @click="handlePower('cover_close')"
                                >关</el-button
                            >
                        </div>
                    </div>

                    <div class="control-card air-condition">
                        <div class="card-header">
                            <span class="card-title">空调</span>
                        </div>
                        <div class="card-content">
                            <el-button
                                size="small"
                                :class="{ active: deviceData.air_conditioner.air_conditioner_state == 0 }"
                                class="toggle-btn"
                                @click="handlePower('air_conditioner_mode_switch', { action: 0 })"
                                >待机</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.air_conditioner.air_conditioner_state == 1 }"
                                class="toggle-btn"
                                @click="handlePower('air_conditioner_mode_switch', { action: 1 })"
                                >制冷</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.air_conditioner.air_conditioner_state == 2 }"
                                class="toggle-btn"
                                @click="handlePower('air_conditioner_mode_switch', { action: 2 })"
                                >制热</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.air_conditioner.air_conditioner_state == 3 }"
                                class="toggle-btn"
                                @click="handlePower('air_conditioner_mode_switch', { action: 3 })"
                                >除湿</el-button
                            >
                        </div>
                    </div>
                </div>

                <!-- 第二行 -->
                <div class="control-row">
                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">静音模式</span>
                        </div>
                        <div class="card-content">
                            <el-button type="primary" size="small" class="toggle-btn active">开</el-button>
                            <el-button size="small" class="toggle-btn">关</el-button>
                        </div>
                    </div>

                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">机场声光警报</span>
                        </div>
                        <div class="card-content">
                            <el-button
                                size="small"
                                :class="{ active: deviceData.alarm_state == 1 }"
                                class="toggle-btn"
                                @click="handlePower('alarm_state_switch', { action: 1 })"
                                >开</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.alarm_state == 0 }"
                                class="toggle-btn"
                                @click="handlePower('alarm_state_switch', { action: 0 })"
                                >关</el-button
                            >
                        </div>
                    </div>

                    <div class="control-card empty-card"></div>

                    <!-- <div class="airport-system">
                        <div class="card-header">
                            <span class="card-title">机场存储</span>
                            <span class="status-text">0.1/73.2GB</span>
                        </div>
                        <div class="status-label">格式化</div>
                    </div> -->
                </div>

                <!-- 第三行 -->
                <!-- <div class="control-row">
                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">机场增强图传</span>
                        </div>
                        <div class="card-content">
                            <el-button type="primary" size="small" class="toggle-btn active">开</el-button>
                            <el-button size="small" class="toggle-btn">关</el-button>
                        </div>
                    </div>

                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">限飞解禁证书</span>
                        </div>
                        <div class="card-content">
                            <el-button type="primary" size="small" class="toggle-btn active">开</el-button>
                            <el-button size="small" class="toggle-btn">关</el-button>
                        </div>
                    </div>

                    <div class="control-card empty-card"></div>

                </div> -->
            </div>
        </div>

        <!-- 飞行器控制区域 -->
        <div class="control-section">
            <div class="section-header">
                <div class="section-title">
                    <span class="title-text">飞行器控制</span>
                </div>
            </div>

            <div class="control-grid">
                <!-- 第一行 -->
                <div class="control-row">
                    <div class="airport-system">
                        <div class="card-header">
                            <span class="card-title">飞行器电源</span>
                            <span class="status-text">{{ deviceData.sub_device.device_online_status == 0 ? '已关机' : '已开机' }}</span>
                        </div>
                        <div
                            class="status-label"
                            @click="handlePower(deviceData.sub_device.device_online_status == 0 ? 'drone_open' : 'drone_close')"
                        >
                            {{ deviceData.sub_device.device_online_status == 0 ? '开机' : '关机' }}
                        </div>
                    </div>

                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">飞行器充电</span>
                        </div>
                        <div class="card-content">
                            <el-button
                                size="small"
                                :class="{ active: deviceData.drone_charge_state.state == 1 }"
                                class="toggle-btn"
                                @click="handlePower('charge_open')"
                                >开</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.drone_charge_state.state == 0 }"
                                class="toggle-btn"
                                @click="handlePower('charge_close')"
                                >关</el-button
                            >
                        </div>
                    </div>

                    <div class="control-card">
                        <div class="card-header">
                            <span class="card-title">增强图传</span>
                        </div>
                        <div class="card-content">
                            <el-button
                                size="small"
                                :class="{ active: deviceData.wireless_link.link_workmode == 0 }"
                                class="toggle-btn"
                                @click="handlePower('sdr_workmode_switch', { link_workmode: 1 })"
                                >开</el-button
                            >
                            <el-button
                                size="small"
                                :class="{ active: deviceData.wireless_link.link_workmode == 1 }"
                                class="toggle-btn"
                                @click="handlePower('sdr_workmode_switch', { link_workmode: 0 })"
                                >关</el-button
                            >
                        </div>
                    </div>
                </div>

                <!-- 第二行 -->
                <!-- <div class="control-row">
                    <div class="airport-system">
                        <div class="card-header">
                            <span class="card-title">飞行器存储</span>
                            <span class="status-text">0.1/73.2GB</span>
                        </div>
                        <div class="status-label">格式化</div>
                    </div>

                    <div class="airport-system">
                        <div class="card-header">
                            <span class="card-title">飞行器增强图传</span>
                        </div>
                        <div class="status-label">设置</div>
                    </div>

                    <div class="control-card empty-card"></div>
                </div> -->
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, watch, inject } from 'vue'
import { DJIoperations } from '/@/utils/mqttSdk'
import { useMqttStore } from '/@/stores/mqtt'
import { ElMessage } from 'element-plus'
import { disposition } from '/@/config/disposition'

const mqttStore = useMqttStore()

const deviceData = computed(() => mqttStore.deviceData)
const gateway_sn = computed(() => mqttStore.gateway_sn)

const livePop = inject<boolean>('livePop')

// 加载中
const loading = ref(false)

// 是否远程调试
const remoteDebug = ref(deviceData.value.mode_code == 2)
watch(remoteDebug, (newVal: any) => {
    loading.value = true
    if (newVal) {
        DJIoperations.sendServices(gateway_sn.value, 'debug_mode_open')
        DJIoperations.deviceEvents(gateway_sn.value)
    } else {
        DJIoperations.sendServices(gateway_sn.value, 'debug_mode_close')
        DJIoperations.deviceEvents(gateway_sn.value)
    }
})

// 监听发送消息回复
const message_services = computed(() => mqttStore.getMessagesByTopic(`thing/product/${gateway_sn.value}/services_reply`))
watch(message_services, (newVal: any) => {
    if (newVal.payload) {
        const data = JSON.parse(newVal.payload).data
        console.log('message_services', data)
        if (data.result != 0) {
            loading.value = false
            ElMessage.error('操作失败')
        } else {
            if (data.output.status == 'ok') {
                loading.value = false
                ElMessage.success('操作成功')
                DJIoperations.deviceEventsClose(gateway_sn.value)
            }
        }
    }
})

// 监听当前任务进度
const message_event = computed(() => mqttStore.getMessagesByTopic(`thing/product/${gateway_sn.value}/events`))
watch(message_event, (newVal: any) => {
    if (newVal.payload) {
        const data = JSON.parse(newVal.payload).data
        console.log('message_event', data)
        if (!data.output) return
        if (data.result != 0) {
            loading.value = false
            ElMessage.error('操作失败')
        } else {
            if (data.output.status === 'ok') {
                loading.value = false
                ElMessage.success('操作成功')
                DJIoperations.deviceEventsClose(gateway_sn.value)
            }
        }
    }
})

watch(deviceData, (newVal: any) => {
    disposition.device.device_sn = newVal.sub_device.device_sn
    disposition.setDeviceVideoId()
    // if (newVal.mode_code == 2) {
    //     remoteDebug.value = true
    // } else {
    //     remoteDebug.value = false
    // }
    console.log('设备状态改变', newVal)
})

const handlePower = (method: string, data?: any) => {
    if (!remoteDebug.value) {
        return ElMessage.warning('请先进入远程调试模式')
    }
    loading.value = true
    DJIoperations.sendServices(gateway_sn.value, method, data)
    DJIoperations.deviceEvents(gateway_sn.value)
}

// 控制状态
// const remoteDebug = ref(true)
</script>

<style scoped>
.config-section {
    padding: 20px;
    border: 1px solid #0000001a;
    border-radius: 16px;
}

/* 顶部控制区域 */
.top-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 16px;
    border-bottom: 1px solid #0000001a;
}

.left-controls {
    display: flex;
    align-items: center;
}

.remote-debug {
    display: flex;
    align-items: center;
    gap: 12px;
}

.control-label {
    font-size: 14px;
    color: #333;
    font-weight: 400;
}

.right-controls {
    display: flex;
    gap: 12px;
}

.flight-test-btn {
    height: 48px;
    padding: 0 32px;
    border-radius: 12px;
    font-size: 16px;
    background: #00386d;
    border-color: #00386d;
    color: #fff;
}

.flight-test-btn:hover {
    background: #2a5a82;
    border-color: #2a5a82;
}

.feedback-btn {
    height: 48px;
    padding: 0 32px;
    border-radius: 12px;
    font-size: 16px;
    border: 1px solid #0000001a;
    color: #000000;
}

.feedback-btn:hover {
    border-color: #1e4d72;
    background: #fff;
    color: #1e4d72;
}

/* 控制区域 */
.control-section {
    padding-bottom: 16px;
    margin-top: 16px;
    border-bottom: 1px solid #0000001a;
}

.section-header {
    margin-bottom: 16px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.title-text {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.live-btn {
    height: 32px;
    padding: 0 12px;
    border-radius: 12px;
    font-size: 14px;
    color: #000;
    border: 1px solid #0000001a;
}

/* 控制网格 */
.control-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.control-row {
    display: flex;
    gap: 12px;
}

.control-card {
    flex: 1;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 16px;
    min-height: 80px;
    display: flex;
    flex-direction: column;
}

.control-card.empty-card {
    background: transparent;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.card-title {
    font-size: 13px;
    color: #333;
    font-weight: 400;
}

.card-content {
    flex: 1;
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 4px;
}

/* 机场系统特殊样式 */
.airport-system {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 16px;
}

.airport-system .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
}

.status-text {
    font-size: 12px;
    color: #2ba471;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #2ba471;
}

.status-label {
    padding: 6px 12px;
    font-size: 12px;
    color: #000;
    border: 1px solid #0000001a;
    border-radius: 12px;
    background: #fff;
    cursor: pointer;
}

.status-label:hover {
    background: #fcfbfb1a;
}

/* 按钮样式 */
.refresh-btn,
.power-btn,
.settings-btn {
    width: 20px;
    height: 20px;
    padding: 0;
    border-radius: 50%;
    background: transparent;
    border: none;
    color: #666;
}

.refresh-btn .el-icon,
.power-btn .el-icon,
.settings-btn .el-icon {
    font-size: 14px;
}

/* 切换按钮组 */
.toggle-buttons {
    display: flex;
    gap: 6px;
}

.toggle-btn {
    flex: 1;
    height: 36px;
    padding: 0 12px;
    font-size: 12px;
    color: #666;
    margin: 0;
    border: 0;
    border-radius: 4px;
}

.toggle-btn.active {
    background: #1e4d72;
    color: white;
}

/* 空调按钮 */
.air-condition .card-content {
    flex-wrap: wrap;
}

.ac-buttons {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
}

.ac-btn {
    height: 22px;
    padding: 0 8px;
    font-size: 11px;
    border-radius: 3px;
    background: white;
    border: 1px solid #d9d9d9;
    color: #666;
    min-width: 32px;
}

.ac-btn.active {
    background: #1e4d72;
    border-color: #1e4d72;
    color: white;
}

/* 存储卡片 */
.storage-card .card-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
}

.storage-info {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
}

.storage-usage {
    font-size: 11px;
    color: #52c41a;
    background: #f6ffed;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #b7eb8f;
}

.format-btn {
    height: 20px;
    padding: 0 6px;
    font-size: 10px;
    border-radius: 3px;
    background: white;
    border: 1px solid #d9d9d9;
    color: #666;
    display: flex;
    align-items: center;
    gap: 2px;
}

.format-btn .el-icon {
    font-size: 10px;
}

/* 飞行器电源 */
.aircraft-power .card-content {
    justify-content: flex-start;
}

.power-status {
    font-size: 12px;
    color: #666;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
}

/* 设置卡片 */
.settings-card .card-content {
    justify-content: flex-start;
}

.settings-text {
    font-size: 12px;
    color: #666;
}
</style>
