<template>
    <transition name="fade">
        <div class="aerodrome-options" v-loading="loading" v-if="isOptions">
            <div class="aerodrome-options-header">
                <h1>机舱设置</h1>
                <el-icon size="20" @click="isOptions = false" style="cursor: pointer"><Close /></el-icon>
            </div>
            <div class="aerodrome-options-content">
                <div class="content-info">
                    <div class="content-info-item">
                        <div class="item-top">机场搜星</div>
                        <div class="item-bottom">{{ deviceData.position_state.rtk_number }}</div>
                    </div>
                    <div class="content-info-item">
                        <div class="item-top">舱内温度</div>
                        <div class="item-bottom">{{ deviceData.temperature }}℃</div>
                    </div>
                    <div class="content-info-item">
                        <div class="item-top">空调状态</div>
                        <div class="item-bottom">
                            {{ airConditionerState[deviceData.air_conditioner.air_conditioner_state] }}
                        </div>
                    </div>
                </div>
                <div class="content-options">
                    <div class="options-header">
                        <span class="options-header-title">远程调试</span>
                        <el-switch
                            @change="handleRemoteDebug"
                            style="--el-switch-on-color: #00386d"
                            v-model="remoteDebug"
                            inline-prompt
                            active-text="开"
                            inactive-text="关"
                        />
                    </div>
                    <div class="options-content">
                        <div class="options-content-item">
                            <div class="item-left">
                                <span class="item-left-title">机场系统</span>
                                <div class="item-left-value">正常工作中</div>
                            </div>
                            <div class="item-right">
                                <el-icon @click="handlePower('reboot')"><RefreshRight /></el-icon>
                                <span>重启</span>
                            </div>
                        </div>
                        <div class="options-content-item1">
                            <div class="item-top">舱盖</div>
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
                        <div class="options-content-item1">
                            <div class="item-top">飞行器电源</div>
                            <div class="card-content">
                                <el-button
                                    size="small"
                                    :class="{ active: deviceData.sub_device.device_online_status == 1 }"
                                    class="toggle-btn"
                                    @click="handlePower('drone_open')"
                                    >开</el-button
                                >
                                <el-button
                                    size="small"
                                    :class="{ active: deviceData.sub_device.device_online_status == 0 }"
                                    class="toggle-btn"
                                    @click="handlePower('drone_close')"
                                    >关</el-button
                                >
                            </div>
                        </div>
                        <div class="options-content-item">
                            <div class="item-left">
                                <span class="item-left-title">飞行器充电</span>
                                <div class="item-left-value">{{ deviceData.drone_charge_state.state == 0 ? '未充电' : '充电中' }}</div>
                            </div>
                            <div class="item-right">
                                <span>充电</span>
                            </div>
                        </div>
                        <div class="options-content-item1" v-if="deviceData.wireless_link">
                            <div class="item-top">4G增强图传</div>
                            <div class="card-content">
                                <el-button
                                    size="small"
                                    :class="{ active: deviceData.wireless_link.link_workmode == 1 }"
                                    class="toggle-btn"
                                    @click="handlePower('sdr_workmode_switch', { link_workmode: 1 })"
                                    >开</el-button
                                >
                                <el-button
                                    size="small"
                                    :class="{ active: deviceData.wireless_link.link_workmode == 0 }"
                                    class="toggle-btn"
                                    @click="handlePower('sdr_workmode_switch', { link_workmode: 0 })"
                                    >关</el-button
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script setup lang="ts">
import { inject, ref, watch, computed } from 'vue'
import { Close, RefreshRight } from '@element-plus/icons-vue'
import { useMqttStore } from '/@/stores/mqtt'
import { DJIoperations } from '/@/utils/mqttSdk'
import { ElMessage } from 'element-plus'
import { errorCode } from '/@/config/eorr'
import { airConditionerState } from '../type/description'
// 加载中
const loading = ref(false)

const mqttStore = useMqttStore()

const deviceData = computed(() => mqttStore.deviceData)

const isOptions = inject<any>('isOptions')

watch(isOptions, (newVal: any) => {
    if (newVal) {
        // 订阅设备服务回复
        DJIoperations.deviceServicesReply(aerodromeInfo.value.sn)
        remoteDebug.value = deviceData.value.mode_code == 2
    } else {
        DJIoperations.deviceEventsClose(aerodromeInfo.value.sn)
        // 关闭订阅设备服务回复
        DJIoperations.deviceServicesReplyClose(aerodromeInfo.value.sn)
        if (loading.value) {
            loading.value = false
        }
    }
})

// 机舱的基本信息
const aerodromeInfo = inject<any>('aerodromeInfo')

const remoteDebug = ref(false)

const handleRemoteDebug = (val: any) => {
    loading.value = true
    if (val) {
        DJIoperations.deviceEvents(aerodromeInfo.value.sn)
        DJIoperations.sendServices(aerodromeInfo.value.sn, 'debug_mode_open')
    } else {
        DJIoperations.deviceEvents(aerodromeInfo.value.sn)
        DJIoperations.sendServices(aerodromeInfo.value.sn, 'debug_mode_close')
    }
}

// 监听发送消息回复
const message_services = computed(() => mqttStore.getMessagesByTopic(`thing/product/${aerodromeInfo.value.sn}/services_reply`))
watch(message_services, (newVal: any) => {
    if (newVal.payload) {
        const data = JSON.parse(newVal.payload).data
        if (data.result != 0) {
            loading.value = false
            ElMessage.error(errorCode[data.result] || '未知错误')
        } else {
            if (data.output && data.output.status == 'ok') {
                loading.value = false
                ElMessage.success('操作成功')
                console.log('航线执行完成:services')

                DJIoperations.deviceEventsClose(aerodromeInfo.value.sn)
            }
        }
    }
})

// 监听当前任务进度
const message_event = computed(() => mqttStore.getMessagesByTopic(`thing/product/${aerodromeInfo.value.sn}/events`))
watch(message_event, (newVal: any) => {
    if (newVal.payload) {
        const data = JSON.parse(newVal.payload).data
        // console.log('message_event', data)
        if (!data.output) return
        if (data.result != 0) {
            loading.value = false
            ElMessage.error(errorCode[data.result] || '未知错误')
        } else {
            if (data.output && data.output.status === 'ok') {
                loading.value = false
                ElMessage.success('操作成功')
                console.log('航线执行完成:events')
                DJIoperations.deviceEventsClose(aerodromeInfo.value.sn)
            }
        }
    }
})

const handlePower = (method: string, data?: any) => {
    if (!remoteDebug.value) {
        return ElMessage.warning('请先进入远程调试模式')
    }
    loading.value = true
    DJIoperations.sendServices(aerodromeInfo.value.sn, method, data)
    DJIoperations.deviceEvents(aerodromeInfo.value.sn)
}
</script>

<style scoped lang="scss">
.aerodrome-options {
    width: 440px;
    position: absolute;
    right: 456px;
    top: 10px;
    background-color: #fff;
    box-shadow: 0px 0px 4px 0px #0000001a;
    border-radius: 12px;
    padding: 16px;
    z-index: 99;

    &-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e5e5;
    }

    &-content {
        display: flex;
        flex-direction: column;
        gap: 16px;

        .content-info {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid #e5e5e5;

            &-item {
                flex: 0 0 calc(100% / 3 - 11px);
                height: 50px;
                background: #f1f5f9;
                border-radius: 10px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                gap: 4px;

                &-top {
                    font-size: 12px;
                    color: #00000066;
                }

                &-bottom {
                    font-size: 14px;
                    color: #000000;
                }
            }
        }

        .content-options {
            display: flex;
            flex-direction: column;
            gap: 12px;

            .options-header {
                display: flex;
                align-items: center;
                gap: 10px;

                &-title {
                    font-size: 14px;
                    color: #000000;
                    line-height: 14px;
                }
            }

            .options-content {
                display: flex;
                flex-wrap: wrap;
                gap: 12px;

                &-item {
                    flex: 0 0 calc(50% - 6px);
                    height: 74px;
                    background: #f1f5f9;
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 4px;
                    padding: 0 12px;
                }

                &-item1 {
                    flex: 0 0 calc(50% - 6px);
                    height: 74px;
                    background: #f1f5f9;
                    border-radius: 10px;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    gap: 4px;
                    padding: 8px 12px;

                    .card-content {
                        flex: 1;
                        display: flex;
                        align-items: center;
                        background: #fff;
                        border-radius: 4px;
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
                }

                .item-top {
                    font-size: 14px;
                    color: #000000;
                    font-weight: bold;
                }

                .item-left {
                    display: flex;
                    flex-direction: column;
                    gap: 10px;

                    &-title {
                        font-size: 14px;
                        color: #000000;
                    }

                    &-value {
                        font-size: 12px;
                        border: 1px solid #2ba471;
                        width: 72px;
                        height: 22px;
                        line-height: 22px;
                        text-align: center;
                        border-radius: 4px;
                        color: #2ba471;
                    }
                }

                .item-right {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    padding: 6px 12px;
                    border-radius: 12px;
                    border: 1px solid #0000001a;
                    background: #ffffff;
                    cursor: pointer;

                    &:hover {
                        background: #e5e5e5;
                    }

                    span {
                        font-size: 14px;
                        color: #000000;
                    }
                }
            }
        }
    }
}
</style>
