<template>
    <transition name="fade" mode="out-in">
        <div v-if="aerodromeSetting" class="right-aerodrome">
            <div class="right-aerodrome-header">
                <div class="right-aerodrome-header-left">
                    <div class="right-aerodrome-header-left-status">
                        <div class="right-aerodrome-header-left-status-item">{{ t(`equipment.mode_code ${deviceData.mode_code}`) }}</div>
                        <!-- <div class="right-aerodrome-header-left-status-line">-</div> -->
                        <!-- <div class="right-aerodrome-header-left-status-item" @click="subscribe('thing/product/7CTXN3S00B08GE/osd')">无任务</div> -->
                    </div>
                    <span class="right-aerodrome-header-left-title">机场</span>
                </div>
                <div class="right-aerodrome-header-right">
                    <el-icon size="20" @click="aerodromeSetting = false" style="cursor: pointer"><Close /></el-icon>
                </div>
            </div>
            <div class="right-aerodrome-content">
                <div class="right-aerodrome-content-item">
                    <div class="right-aerodrome-content-item-left">
                        <img src="/img/dashboard/jc.png" alt="" class="right-aerodrome-content-item-left-icon" />
                        <span class="right-aerodrome-content-item-left-title">{{ aerodromeInfo.nickname }}</span>
                    </div>
                    <div class="right-aerodrome-content-item-right">
                        <div class="right-aerodrome-content-item-right-item">
                            <div class="right-aerodrome-content-item-right-item-left">{{ t(`equipment.mode_code ${deviceData.mode_code}`) }}</div>
                            <div class="right-aerodrome-content-item-right-item-right">当前正常</div>
                        </div>
                        <div class="right-aerodrome-content-item-right-item">
                            <div class="right-aerodrome-content-item-right-item-box">
                                <img src="/img/image/tabs1.png" class="right-aerodrome-content-item-right-item-box-icon" alt="" />
                                <span class="right-aerodrome-content-item-right-item-box-text">适合飞行</span>
                            </div>
                            <div class="right-aerodrome-content-item-right-item-box">
                                <img src="/img/image/tabs1.png" class="right-aerodrome-content-item-right-item-box-icon" alt="" />
                                <span class="right-aerodrome-content-item-right-item-box-text">{{ deviceData.temperature }}℃</span>
                            </div>
                            <div class="right-aerodrome-content-item-right-item-box">
                                <img src="/img/image/tabs1.png" class="right-aerodrome-content-item-right-item-box-icon" alt="" />
                                <span class="right-aerodrome-content-item-right-item-box-text" v-if="deviceData.network_state"
                                    >{{ deviceData.network_state.rate }}kb/s</span
                                >
                            </div>
                            <div class="right-aerodrome-content-item-right-item-box">
                                <img src="/img/image/tabs1.png" class="right-aerodrome-content-item-right-item-box-icon" alt="" />
                                <span class="right-aerodrome-content-item-right-item-box-text" v-if="deviceData.position_state">{{
                                    deviceData.position_state.rtk_number
                                }}</span>
                            </div>
                        </div>
                        <div class="right-aerodrome-content-item-right-item">
                            <div class="right-aerodrome-content-item-right-item-button" @click="isShowCabinLive = true">机场直播</div>
                            <div
                                :class="{ active: isOptions }"
                                class="right-aerodrome-content-item-right-item-opention"
                                @click="isOptions = !isOptions"
                            >
                                <el-tooltip class="box-item" effect="dark" content="机舱控制" placement="top">
                                    <el-icon size="16"><Operation /></el-icon>
                                </el-tooltip>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="right-aerodrome-content-item">
                    <div class="right-aerodrome-content-item-left">
                        <img src="/img/dashboard/wrj.png" alt="" class="right-aerodrome-content-item-left-icon" />
                        <span class="right-aerodrome-content-item-left-title">Dock 2</span>
                    </div>
                    <div class="right-aerodrome-content-item-right">
                        <div class="right-aerodrome-content-item-right-item">
                            <div class="right-aerodrome-content-item-right-item-left" v-if="deviceData.sub_device">
                                {{ deviceData.drone_in_dock === 0 ? '舱外' : '舱内'
                                }}{{ deviceData.sub_device.device_online_status === 0 ? '关闭' : '开启' }}
                            </div>
                            <div class="right-aerodrome-content-item-right-item-right">N/A</div>
                        </div>
                        <div class="right-aerodrome-content-item-right-item" v-if="deviceData.sub_device">
                            <div class="right-aerodrome-content-item-right-item-button" @click="tapDronePop">
                                {{ deviceData.sub_device.device_online_status == 0 ? '当前设备已关机，无法进行直播' : '无人机直播' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
    <!-- <transition name="fade">
        <aerodrome-options v-if="isOptions" />
    </transition> -->
    <aerodrome-options />
</template>

<script setup lang="ts">
import { ref, onMounted, computed, inject, reactive, watch, provide } from 'vue'
import { useMqttStore } from '/@/stores/mqtt'
import { Operation, Close } from '@element-plus/icons-vue'
import { DJIoperations } from '/@/utils/mqttSdk'
import AerodromeOptions from './aerodrome-options.vue'
import { ElMessage } from 'element-plus'
import { disposition } from '/@/config/disposition'
import { useI18n } from 'vue-i18n'
import { flighttask_step_code } from '../type/description'
import { useMapStore } from '/@/stores/map'
import { storeToRefs } from 'pinia'

const { t } = useI18n()

const mapStore = useMapStore()
const { isShowDroneLive, isShowCabinLive } = storeToRefs(mapStore)

// 机场信息
const mqttStore = useMqttStore()

// 当前设备详情
const deviceData = computed(() => mqttStore.deviceData)

// 机场设置
const aerodromeSetting = inject<any>('aerodromeSetting')
// 机舱设置
const isOptions = ref(false)
provide('isOptions', isOptions)
// 机场基本信息
const aerodromeInfo = inject<any>('aerodromeInfo')

watch(aerodromeSetting, (newVal: any) => {
    if (!newVal) {
        isOptions.value = false
    }
})

// 订阅设备拓扑更新
// const statusMessage = computed(() => {
//     return DJIoperations.statusMessage(aerodromeInfo.value.sn)
// })
// watch(statusMessage, (newVal: any) => {
//     if (newVal.payload) {
//         console.log('设备拓扑更新', JSON.parse(newVal.payload))
//     }
// })

// 打开无人机直播
const tapDronePop = () => {
    if (deviceData.value.sub_device && deviceData.value.sub_device.device_online_status == 1) {
        isShowDroneLive.value = true
    } else {
        ElMessage.warning('当前设备已关机，无法进行直播')
    }
}

onMounted(() => {})
</script>

<style scoped lang="scss">
.right-aerodrome {
    width: 440px;
    position: absolute;
    right: 10px;
    top: 10px;
    background-color: #fff;
    box-shadow: 0px 0px 4px 0px #0000001a;
    border-radius: 12px;
    padding: 16px;
    z-index: 99;

    &-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e5e5;

        &-left {
            display: flex;
            align-items: center;
            gap: 10px;

            &-status {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 8px;
                border: 1px solid #00000026;
                border-radius: 4px;
                min-width: 160px;

                &-item {
                    flex: 1;
                    border-radius: 4px;
                    border: 1px solid #00000099;
                    padding: 2px 6px;
                    color: #00000099;
                    font-size: 12px;
                    text-align: center;
                }

                &-line {
                    color: #00000099;
                }
            }

            &-title {
                font-size: 14px;
            }
        }
    }

    &-content {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 16px;

        &-item {
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #e5e5e5;
            padding-bottom: 16px;

            &-left {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 10px;

                &-icon {
                    width: 60px;
                    height: 60px;
                    border-radius: 4px;
                }

                &-title {
                    font-size: 14px;
                }
            }

            &-right {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 10px;

                &-item {
                    display: flex;
                    border-radius: 4px;
                    overflow: hidden;

                    &-left {
                        background: #0000001a;
                        flex: 1;
                        font-size: 12px;
                        padding: 4px 6px;
                        color: #2ba471;
                    }

                    &-right {
                        flex: 1;
                        font-size: 12px;
                        padding: 4px 6px;
                        background: #00000026;
                    }

                    &-button {
                        flex: 1;
                        font-size: 12px;
                        color: #00000099;
                        border: 1px solid #00000026;
                        text-align: center;
                        padding: 6px 0;
                        cursor: pointer;
                        border-radius: 4px;
                    }

                    &-opention {
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        border-radius: 4px;
                        border: 1px solid #00000026;
                        margin-left: 10px;

                        &.active {
                            border: 1px solid #00386d;
                            background: #e0f6ff;
                        }
                    }

                    &-box {
                        flex: 1;
                        display: flex;
                        align-items: center;
                        background: #0000001a;
                        gap: 4px;
                        padding: 4px 6px;

                        &-icon {
                            width: 16px;
                            height: 16px;
                        }

                        &-text {
                            font-size: 12px;
                            color: #00000099;
                            white-space: nowrap;
                        }
                    }
                }
            }
        }
    }
}

.fade-enter-active {
    animation: fadeIn 0.5s ease forwards;
}

.fade-leave-active {
    animation: fadeOut 0.5s ease forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(20px);
    }
}
</style>
