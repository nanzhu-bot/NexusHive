<template>
    <el-dialog
        :model-value="['Setting'].includes(baTable.form.operate!)"
        title=""
        width="800px"
        @close="close"
        class="aircraft-detail-dialog"
        :close-on-click-modal="false"
        align-center
    >
        <!-- 使用 Element Plus Tabs 作为标题栏 -->
        <template #header>
            <div class="dialog-header">
                <el-tabs v-model="activeTab" class="dialog-tabs">
                    <el-tab-pane label="状态" name="status"></el-tab-pane>
                    <el-tab-pane label="配置" name="config"></el-tab-pane>
                </el-tabs>
            </div>
        </template>

        <!-- 弹窗内容 -->
        <div class="dialog-content">
            <!-- 状态标签页 -->
            <div v-if="activeTab === 'status'">
                <StatusSection :device-data="deviceData" :status-data="statusData" />
            </div>

            <!-- 配置标签页 -->
            <div v-else-if="activeTab === 'config'">
                <ConfigSection :device-data="deviceData" :config-data="configData" />
            </div>
        </div>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, computed, inject, watch, onMounted } from 'vue'
import StatusSection from '../components/StatusSection.vue'
import ConfigSection from '../components/ConfigSection.vue'
import type baTableClass from '/@/utils/baTable'
import { DJIoperations } from '/@/utils/mqttSdk'
import { useMqttStore } from '/@/stores/mqtt'

interface Props {
    aircraftData?: any
}

const props = withDefaults(defineProps<Props>(), {
    aircraftData: null,
})

const baTable = inject('baTable') as baTableClass

watch(
    () => baTable.form.operate,
    (newVal: string | undefined) => {
        if (newVal === 'Setting') {
            DJIoperations.osd(baTable.form.operateIds![0])
            mqttStore.gateway_sn = baTable.form.operateIds![0]
            // 订阅设备服务回复
            DJIoperations.deviceServicesReply(baTable.form.operateIds![0])
        }
    }
)

const mqttStore = useMqttStore()

const messages = computed(() => mqttStore.getMessagesByTopic(`thing/product/${baTable.form.operateIds![0]}/osd`))

watch(messages, (newVal: any) => {
    if (newVal.payload) {
        mqttStore.deviceData = {
            ...mqttStore.deviceData,
            ...JSON.parse(newVal.payload).data,
        }
    }
})

const activeTab = ref('status')

// 设备基础数据
const deviceData = computed(() => ({
    name: props.aircraftData?.name || 'Dock 2 V14.01.0407',
    model: props.aircraftData?.model || 'DJI-M3D',
    sn: props.aircraftData?.sn || '7CT55FSS5FSS4F5S',
    firmware: props.aircraftData?.firmware || 'V14.01.0407',
    updateTime: props.aircraftData?.updateTime || '2025/07/16 03:56',
}))

// 状态数据
const statusData = computed(() => ({
    workStatus: props.aircraftData?.workStatus || '空闲中',
    temperature: props.aircraftData?.temperature || '正常',
    batteryLevel: props.aircraftData?.batteryLevel || 85,
    signalStrength: props.aircraftData?.signalStrength || '强',
    gpsStatus: props.aircraftData?.gpsStatus || '已连接',
    flightTime: props.aircraftData?.flightTime || '6h8min',
    altitude: props.aircraftData?.altitude || '120m',
    speed: props.aircraftData?.speed || '15m/s',
    voltage: props.aircraftData?.voltage || '16.2V',
    current: props.aircraftData?.current || '2.5A',
    power: props.aircraftData?.power || '40W',
    windSpeed: props.aircraftData?.windSpeed || '3m/s',
    cameraStatus: props.aircraftData?.cameraStatus || '正常',
    gimbalStatus: props.aircraftData?.gimbalStatus || '正常',
    rcSignal: props.aircraftData?.rcSignal || '强',
    satelliteCount: props.aircraftData?.satelliteCount || 12,
}))

// 配置数据
const configData = computed(() => ({
    flightMode: props.aircraftData?.flightMode || '自动模式',
    maxAltitude: props.aircraftData?.maxAltitude || '500m',
    maxDistance: props.aircraftData?.maxDistance || '2000m',
    returnHomeAltitude: props.aircraftData?.returnHomeAltitude || '100m',
    lowBatteryWarning: props.aircraftData?.lowBatteryWarning || '30%',
    criticalBatteryAction: props.aircraftData?.criticalBatteryAction || '自动返航',
    gpsMode: props.aircraftData?.gpsMode || 'RTK模式',
    compassCalibration: props.aircraftData?.compassCalibration || '已校准',
    gimbalMode: props.aircraftData?.gimbalMode || '跟随模式',
    cameraSettings: props.aircraftData?.cameraSettings || '自动',
    videoResolution: props.aircraftData?.videoResolution || '4K@30fps',
    photoFormat: props.aircraftData?.photoFormat || 'JPEG+RAW',
    rcMode: props.aircraftData?.rcMode || '模式2',
    failsafeAction: props.aircraftData?.failsafeAction || '返航',
    ledIndicator: props.aircraftData?.ledIndicator || '开启',
    // 电池参数
    batteryVoltage: props.aircraftData?.batteryVoltage || '16.2V',
    batteryCurrent: props.aircraftData?.batteryCurrent || '2.5A',
    batteryTemperature: props.aircraftData?.batteryTemperature || '29.9℃',
    batteryCapacity: props.aircraftData?.batteryCapacity || '5000mAh',
    batteryCycles: props.aircraftData?.batteryCycles || '13次',
}))

const close = () => {
    DJIoperations.osdClose(baTable.form.operateIds![0])
    DJIoperations.deviceServicesReplyClose(baTable.form.operateIds![0])
    baTable.toggleForm()
}
</script>

<style scoped>
/* 弹窗样式重置 */
:deep(.aircraft-detail-dialog) {
    border-radius: 12px;
    overflow: hidden;
}

/* 使用 Element Plus Tabs 的标题栏 */
.dialog-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
}

.dialog-tabs {
    flex: 1;
}

:deep(.dialog-tabs .el-tabs__item) {
    padding: 8px 0;
    margin-right: 40px;
    border: none;
    background: transparent;
    color: rgba(0, 0, 0, 0.4);
    font-size: 24px;
    font-weight: 600;
    height: auto;
    line-height: 1;
}

:deep(.dialog-tabs .el-tabs__item.is-active) {
    color: #000;
    font-weight: 600;
}

:deep(.dialog-tabs .el-tabs__active-bar) {
    height: 2px;
    background-color: #000;
}
</style>
