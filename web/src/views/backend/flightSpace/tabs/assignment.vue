<template>
    <div class="assignment">
        <div class="assignment-header">
            <div class="assignment-header-left">机场</div>
            <el-button type="primary" @click="handleAddTask">新建任务</el-button>
        </div>
        <!--  -->
        <div v-if="aerodromeList.length > 0" class="assignment-list">
            <div
                v-for="(item, index) in aerodromeList"
                :key="index"
                :class="{ active: currentEditIndex === index }"
                class="assignment-item"
                @click="handleEdit(item, index)"
            >
                <div class="item-left">
                    <div class="item-head">
                        <div class="item-head-left">{{ item.nickname }}</div>
                    </div>
                    <div class="item-content">
                        <div class="item-content-item">
                            <div class="item-content-item-left">设备{{ t(`equipment.mode_code ${deviceOsds[item.sn]?.mode_code}`) }}</div>
                            <div class="item-content-item-right">当前正常</div>
                        </div>
                        <div class="item-content-item">
                            <div class="item-content-item-left">{{ getDroneStatus(item.sn) }}</div>
                            <div class="item-content-item-right">当前正常</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <el-empty v-else description="暂无设备">
            <el-button type="primary" @click="handleAddDevice">添加设备</el-button>
        </el-empty>
    </div>
</template>

<script setup lang="ts">
import { computed, inject, onMounted, ref, watch, provide } from 'vue'
import { useMqttStore } from '/@/stores/mqtt'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { DJIoperations } from '/@/utils/mqttSdk'
import { useProjectStore } from '/@/stores/project'
import { useRouter } from 'vue-router'
import { useMapStore } from '/@/stores/map'
import { drone_status } from '../type/description'

// onMounted(() => {
//     if (aerodromeList.value.length > 0) {
//         handleEdit(aerodromeList.value[0], 0)
//     }
// })

const router = useRouter()
const projectStore = useProjectStore()
const { currentProject, aerodromeList } = storeToRefs(projectStore)

const { t } = useI18n()

const mqttStore = useMqttStore()
const mapStore = useMapStore()
const { isShowLive, graphic_d, initialHeight } = storeToRefs(mapStore)

const { gateway_sn, deviceOsds, droneOsds } = storeToRefs(mqttStore)

// watch(isShowLive, (newVal: any) => {
//     dronePop.value = newVal
// })

// const message = computed(() => mqttStore.messages)

// watch(message, (newVal: any) => {
//     console.log(JSON.parse(newVal.payload).data)
// })

watch(currentProject, () => {
    projectStore.getAerodromeList()
})

// 机场基本信息
const aerodromeInfo = inject<any>('aerodromeInfo')

const isShowLeft = inject('isShowLeft')

// 当前编辑的机场
const currentEditIndex = ref<number | null>(null)

// 打开新建任务弹窗
const isShowAssignmentPop = inject<any>('isShowAssignmentPop')

// 打开任务弹窗
const openAssignmentPop = inject<any>('openAssignmentPop')
watch(openAssignmentPop, (newVal: any) => {
    if (!newVal) {
        currentEditIndex.value = null
    } else {
    }
})

// 编辑机场
const handleEdit = (item: any, index: number) => {
    if (currentEditIndex.value === index) {
        openAssignmentPop.value = false
        return
    } else {
        aerodromeInfo.value = item
        openAssignmentPop.value = true
        currentEditIndex.value = index
        gateway_sn.value = item.sn
        const graphic = graphic_d.value.filter((item1: any) => item1.attr.sn === item.sn)
        initialHeight.value = parseFloat(graphic[0]._point._alt)
    }
}

// 新建任务
const handleAddTask = () => {
    mapStore.deleteRoute()

    currentEditIndex.value = null
    openAssignmentPop.value = false
    isShowLeft.value = false
    isShowAssignmentPop.value = true
    // aerodromeInfo.value = item
    // gateway_sn.value = item.sn
    // const graphic = graphic_d.value.filter((item1: any) => item1.attr.sn === item.sn)
    // initialHeight.value = parseFloat(graphic[0]._point._alt)
}

// 添加设备
const handleAddDevice = () => {
    router.push('/admin/equipment')
}

// 获取飞行器状态
const getDroneStatus = (sn: string) => {
    // console.log(deviceOsds.value[sn].sub_device.device_sn, droneOsds.value[deviceOsds.value[sn].sub_device.device_sn].mode_code)

    if (deviceOsds.value[sn].sub_device) {
        if (deviceOsds.value[sn].sub_device.device_online_status === 1 && droneOsds.value[deviceOsds.value[sn].sub_device.device_sn].mode_code) {
            return drone_status[droneOsds.value[deviceOsds.value[sn].sub_device.device_sn].mode_code]
        } else if (
            deviceOsds.value[sn].sub_device.device_online_status === 1 &&
            !droneOsds.value[deviceOsds.value[sn].sub_device.device_sn].mode_code
        ) {
            return '舱内开机'
        } else {
            return '舱内关机'
        }
    }
    return '舱内关机'
}
</script>

<style scoped lang="scss">
.assignment {
    width: 100%;
    height: 100%;

    &-header {
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
        margin-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    &-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    &-item {
        display: flex;
        padding: 10px 5px;
        gap: 10px;
        cursor: pointer;
        border-radius: 4px;
        border: 1px solid transparent;

        &:hover {
            background: #0000001a;
        }

        &.active {
            background: #f3f9ff;
            border-color: #00386d;
        }

        .item-left {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .item-head {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;

            &-left {
                font-size: 14px;
                font-weight: bold;
            }
        }

        .item-content {
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
            }
        }
    }
}
</style>
