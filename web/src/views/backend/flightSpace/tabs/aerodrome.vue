<template>
    <div class="aerodrome">
        <div class="aerodrome-header">机场</div>
        <!--  -->
        <div v-if="aerodromeList.length > 0">
            <div v-for="(item, index) in aerodromeList" :key="index" class="aerodrome-item">
                <div class="item-head">
                    <div class="item-head-left">{{ item.nickname }}</div>
                    <el-tooltip class="box-item" effect="dark" content="机场操作" placement="top">
                        <div :class="{ active: currentEditIndex === index }" class="item-head-right" @click="handleEdit(item, index)">
                            <el-icon size="16"><Operation /></el-icon>
                        </div>
                    </el-tooltip>
                </div>
                <div class="item-content">
                    <div class="item-content-item">
                        <div class="item-content-item-left">设备{{ t(`equipment.mode_code ${deviceOsds[item.sn]?.mode_code}`) }}</div>
                        <div class="item-content-item-right">当前正常</div>
                    </div>
                    <div class="item-content-item" v-if="deviceOsds[item.sn].sub_device">
                        <div class="item-content-item-left">{{ getDroneStatus(item.sn) }}</div>
                        <div class="item-content-item-right">当前正常</div>
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
import { inject, onMounted, onUnmounted, provide, ref, watch, computed } from 'vue'
import { useMqttStore } from '/@/stores/mqtt'
import { baTableApi } from '/@/api/common'
import { Operation } from '@element-plus/icons-vue'
import { disposition } from '/@/config/disposition'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useProjectStore } from '/@/stores/project'
import * as mars3d from 'mars3d'
import { useRouter } from 'vue-router'
import { useMapStore } from '/@/stores/map'
import { drone_status } from '../type/description'

const { t } = useI18n()
const router = useRouter()
const map = inject('marsMap')
const mapStore = useMapStore()

const { graphic_d, graphicLayer_d } = storeToRefs(mapStore)

const mqttStore = useMqttStore()

const { gateway_sn, deviceOsds, droneOsds } = storeToRefs(mqttStore)

const projectStore = useProjectStore()
const { currentProject, aerodromeList } = storeToRefs(projectStore)

watch(currentProject, async (val) => {
    await projectStore.getAerodromeList()
})

// 机场地图poi点
const graphicLayer = ref()

// 机场基本信息
const aerodromeInfo = inject<any>('aerodromeInfo')

// 打开机场设置
const aerodromeSetting = inject<any>('aerodromeSetting')

// 当前编辑的机场
const currentEditIndex = ref<number | null>(null)

watch(aerodromeSetting, (newVal: any) => {
    if (!newVal) {
        currentEditIndex.value = null
    }
})

// 查看机场
const handleEdit = (item: any, index: number) => {
    if (currentEditIndex.value === index) {
        aerodromeSetting.value = false
        gateway_sn.value = ''
        disposition.djiDock.gateway_sn = ''
        disposition.device.device_sn = ''
        return
    } else {
        if (aerodromeSetting.value) {
            aerodromeSetting.value = false
            setTimeout(() => {
                aerodromeSetting.value = true
                currentEditIndex.value = index
            }, 500)
        } else {
            aerodromeSetting.value = true
            currentEditIndex.value = index
        }
        aerodromeInfo.value = item
        disposition.djiDock.gateway_sn = item.sn
        gateway_sn.value = item.sn
        disposition.device.device_sn = deviceOsds.value[item.sn].sub_device.device_sn
    }
}

// 添加机场点位
const addAerodromePoint = () => {
    aerodromeList.value.forEach((item: any) => {
        if (deviceOsds.value[item.sn]) {
            item.points = {
                longitude: deviceOsds.value[item.sn].longitude,
                latitude: deviceOsds.value[item.sn].latitude,
            }
            addMapPoint(item)
        }
    })
}

// 添加地图poi点
const addMapPoint = (item: any) => {
    const graphic = new mars3d.graphic.DivGraphic({
        position: [item.points.longitude, item.points.latitude],
        style: {
            html: `<img src="/src/assets/jicang-icon.png" alt="icon" style="width: 60px; height: 60px;" />`,
            horizontalOrigin: mars3d.Cesium.HorizontalOrigin.CENTER,
            verticalOrigin: mars3d.Cesium.VerticalOrigin.BOTTOM,
            distanceDisplayCondition: new mars3d.Cesium.DistanceDisplayCondition(0, 400000), // 按视距距离显示
            clampToGround: true,
        },
        popup: `机场名称：${item.nickname}`,
        attr: {
            sn: item.sn,
        },
    })
    graphicLayer_d.value?.addGraphic(graphic)
    graphic_d.value.push(graphic)
}

// 添加设备
const handleAddDevice = () => {
    router.push('/admin/equipment')
}

// 获取飞行器状态
const getDroneStatus = (sn: string) => {
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

onMounted(async () => {
    await projectStore.getAerodromeList()
    if (graphic_d.value.length === 0) {
        addAerodromePoint()
    }
})
</script>
<style scoped lang="scss">
.aerodrome {
    width: 100%;
    height: 100%;

    &-header {
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    &-item {
        display: flex;
        flex-direction: column;
        padding: 10px;
        gap: 10px;
        background-color: #fff;
        border-radius: 6px;

        &:hover {
            box-shadow: 0px 0px 4px 0px #0000001a;
        }

        .item-head {
            width: 100%;
            display: flex;
            justify-content: space-between;

            &-left {
                font-size: 14px;
                font-weight: bold;
            }

            &-right {
                width: 24px;
                height: 24px;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                border: 1px solid #fff;
                cursor: pointer;

                &.active {
                    border: 1px solid #00386d;
                    background: #e0f6ff;
                }
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
