import { defineStore } from 'pinia'
import { ref, computed, watch } from 'vue'
import * as mars3d from 'mars3d'
import { useMqttStore } from '/@/stores/mqtt'
import { storeToRefs } from 'pinia'
import { DJIoperations } from '/@/utils/mqttSdk'
import { useShipLanes } from '/@/stores/shipLanes'
import { ElMessage } from 'element-plus'
import { errorCode } from '/@/config/eorr'

export const useMapStore = defineStore('map', () => {
    const mqttStore = useMqttStore()
    const shipLanesStore = useShipLanes()
    const { gateway_sn, deviceData, droneData } = storeToRefs(mqttStore)
    const { shipStartPoint } = storeToRefs(shipLanesStore)

    watch(gateway_sn, (newVal: any) => {
        if (newVal) {
            console.log('当前设备sn', newVal)
            console.log('当前设备飞行器sn', deviceData.value.sub_device.device_sn)
        }
    })

    // 定时器
    const timer = ref<any>(null)

    // 是否显示机舱直播
    const isShowCabinLive = ref(false)

    // 是否显示飞行器直播
    const isShowDroneLive = ref(false)
    // 初始高度
    const initialHeight = ref(0)
    // 飞行器是否开机
    const isDronePowerOn = ref(false)

    // 航线
    const graphicLayer_m = ref<mars3d.layer.GraphicLayer>()
    // 任务
    const graphicLayer_w = ref<mars3d.layer.GraphicLayer>()
    // 航点
    const graphicLayer_h = ref<mars3d.layer.GraphicLayer>()
    // 航线
    const graphicLayer_l = ref<mars3d.layer.GraphicLayer>()
    // 航点
    const graphicLayer_p = ref<mars3d.layer.GraphicLayer>()
    // 航线
    const graphicLayer_s = ref<mars3d.layer.GraphicLayer>()
    // 时序坐标
    const graphicLayer_t = ref<mars3d.layer.GraphicLayer>()
    // 设备点位
    const graphicLayer_d = ref<mars3d.layer.GraphicLayer>()

    // 时序路线实例
    const graphic_t = ref<mars3d.graphic.PathEntity>()
    // 设备点位实例列表
    const graphic_d = ref<mars3d.graphic.DivLightPoint[]>([])

    // 是否开始漫游
    const isStartRoam = ref(false)

    const message_event = computed(() => {
        if (gateway_sn.value) {
            return mqttStore.getMessagesByTopic(`thing/product/${gateway_sn.value}/events`)
        }
        return {}
    })

    watch(message_event, (newVal: any) => {
        if (newVal.payload) {
            const data = JSON.parse(newVal.payload)
            // console.log(data.method, data.data)

            // 航线执行完成
            if (data.method === 'flighttask_progress' && data.data.output.status === 'ok') {
                ElMessage.success('航线执行完成')
                isDronePowerOn.value = false
                DJIoperations.deviceEventsClose(gateway_sn.value)
                if (timer.value) {
                    clearInterval(timer.value)
                    timer.value = null
                }
            }

            // 航线报错
            if (data.data.result && data.data.result != 0) {
                ElMessage.closeAll()
                isDronePowerOn.value = false
                ElMessage.error(errorCode[data.data.result as keyof typeof errorCode] || '未知错误')
                DJIoperations.deviceEventsClose(gateway_sn.value)
                if (timer.value) {
                    clearInterval(timer.value)
                    timer.value = null
                }
            }
        }
    })

    // 重置地图
    const resetMap = () => {
        graphicLayer_m.value?.clear()
        graphicLayer_w.value?.clear()
        graphicLayer_h.value?.clear()
        graphicLayer_l.value?.clear()
        graphicLayer_p.value?.clear()
        graphicLayer_s.value?.clear()
        graphicLayer_t.value?.clear()
        graphicLayer_d.value?.clear()
        graphic_d.value = []
    }

    // 删除漫游路线
    const deleteRoute = () => {
        graphicLayer_t.value?.clear()
        graphic_t.value = undefined
        isDronePowerOn.value = false
        if (timer.value) {
            clearInterval(timer.value)
            timer.value = null
        }
    }

    // 创建漫游路线
    const createRoute = () => {
        DJIoperations.deviceEvents(gateway_sn.value)
        graphic_t.value = new mars3d.graphic.PathEntity({
            position: {
                type: 'time', // 时序动态坐标
                forwardExtrapolationType: mars3d.Cesium.ExtrapolationType.HOLD, // 在最后1个结束时间之后，NONE时不显示，HOLD时显示结束时间对应坐标位置
            },
            // maxCacheCount: -1,
            style: {
                width: 2,
                color: '#00ffff',
                opacity: 1.0,
                leadTime: 0, // 前方的路线不显示
                // 高亮时的样式（默认为鼠标移入，也可以指定type:'click'单击高亮），构造后也可以openHighlight、closeHighlight方法来手动调用
                highlight: {
                    type: mars3d.EventType.click,
                    color: '#ff0000',
                },
            },
            label: {
                text: '',
                font_size: 18,
                font_family: '楷体',
                color: mars3d.Cesium.Color.AZURE,
                outline: true,
                outlineColor: mars3d.Cesium.Color.BLACK,
                outlineWidth: 2,
                horizontalOrigin: mars3d.Cesium.HorizontalOrigin.CENTER,
                verticalOrigin: mars3d.Cesium.VerticalOrigin.BOTTOM,
                pixelOffset: new mars3d.Cesium.Cartesian2(10, -25), // 偏移量
            },
            model: {
                url: '/model/dajiang.gltf',
                scale: 1,
                minimumPixelSize: 30,
            },
            attr: { remark: '示例3' },
        })
        graphicLayer_t.value?.addGraphic(graphic_t.value)
        isStartRoam.value = true
        ElMessage({
            message: '等待飞行器连接...',
            type: 'warning',
            duration: 0,
        })

        timer.value = setInterval(() => {
            // console.log(droneData.value)
            if (!droneData.value.height) {
                return
            }
            // 飞行器已开机
            if (deviceData.value.sub_device.device_online_status == 1 && !isDronePowerOn.value) {
                isDronePowerOn.value = true
                ElMessage.closeAll()
                isShowDroneLive.value = true
            }
            // console.log(initialHeight.value, droneData.value.height)
            changePosition({
                longitude: droneData.value.longitude,
                latitude: droneData.value.latitude,
                height: initialHeight.value < droneData.value.height ? droneData.value.height : initialHeight.value,
            })
        }, 2000)
    }

    // 改变位置
    const changePosition = (position: any) => {
        const positions = mars3d.Cesium.Cartesian3.fromDegrees(position.longitude, position.latitude, position.height)
        graphic_t.value?.addTimePosition(positions, 2)
    }

    // 停止漫游
    const stopRoam = () => {
        clearInterval(timer.value)
        timer.value = null
    }

    return {
        // 地图
        graphicLayer_m,
        graphicLayer_w,
        graphicLayer_h,
        graphicLayer_l,
        graphicLayer_p,
        graphicLayer_s,
        graphicLayer_t,
        graphicLayer_d,
        graphic_t,
        graphic_d,
        isStartRoam,
        isShowCabinLive,
        isShowDroneLive,
        initialHeight,
        // 方法
        resetMap,
        createRoute,
        stopRoam,
        deleteRoute,
    }
})
