<template>
    <div class="flight-space">
        <!-- 项目 -->
        <Project v-if="isShowProject" />
        <!-- 添加项目 -->
        <transition name="fade">
            <AddProject v-if="addProjectPop" />
        </transition>

        <!-- 新建任务 -->
        <transition name="fade">
            <AddAssignment1 v-if="isShowAssignmentPop" />
        </transition>

        <!-- 左侧选择卡 -->
        <transition name="fade">
            <LeftTabs v-if="['', 'look'].includes(openShipLanesOptionsPop) && isShowLeft" />
        </transition>

        <!-- 航线配置 -->
        <transition name="fade">
            <ShipLanesOptions v-if="['add', 'edit'].includes(openShipLanesOptionsPop) && isShowLeft" />
        </transition>
        <!-- 航点编辑 -->
        <transition name="fade">
            <WaypointEdit v-if="isEditWaypoint" />
        </transition>

        <!-- 右侧机场 -->
        <RightAerodrome />

        <!-- 添加航线库弹窗 -->
        <AddShip />

        <!-- 右侧媒体库 -->
        <transition name="fade">
            <RightMediaLibrary v-if="activeTab === 3" />
        </transition>

        <!-- 右侧任务 -->
        <transition name="fade">
            <RightAssignment v-if="activeTab === 2 && openAssignmentPop" />
        </transition>

        <!-- 地图 -->
        <Map :url="configUrl" :options="options" map-key="test" @onload="marsOnload" />
        <MapOption />

        <!-- 航线基本信息 -->
        <ShipBaseInfo v-if="openShipLanesOptionsPop !== ''" />

        <!-- 无人机直播 -->
        <DronePop />

        <!-- 机舱直播 -->
        <CabinPop />
    </div>
</template>

<script setup lang="ts">
import { provide, ref, watch, reactive, computed, onMounted, onUnmounted } from 'vue'
import LeftTabs from './components/left-tabs.vue'
import RightAerodrome from './components/right-aerodrome.vue'
import RightMediaLibrary from './components/right-media-library.vue'
import RightAssignment from './components/assignment-table.vue'
import Map from './map/index.vue'
import * as mars3d from 'mars3d'
import AddShip from './components/add-ship.vue'
import ShipLanesOptions from './components/shipLanes-options.vue'
import MapOption from './components/map-option.vue'
import { useShipLanes } from '/@/stores/shipLanes'
import WaypointEdit from './components/waypoint-edit.vue'
import { useMapStore } from '/@/stores/map'
import { storeToRefs } from 'pinia'
import { useProjectStore } from '/@/stores/project'
import Project from './project/index.vue'
import AddProject from './project/add.vue'
import AddAssignment1 from './components/add-assignment1.vue'
import CabinPop from './components/cabinPop.vue'
import DronePop from './components/dronePop.vue'
import { useRoute } from 'vue-router'
import ShipBaseInfo from './components/ship-base-info.vue'
import { useMqttStore } from '/@/stores/mqtt'

// 项目
const projectStore = useProjectStore()
const { isShowProject, addProjectPop, currentProject } = storeToRefs(projectStore)

// 是否显示左侧
const isShowLeft = ref(false)
provide('isShowLeft', isShowLeft)

// 地图
const mapStore = useMapStore()
const { graphicLayer_t, graphicLayer_d, isShowCabinLive, isShowDroneLive } = storeToRefs(mapStore)

// 地图配置
const configUrl = 'config/mapConfig.json'
const options = ref({
    scene: {
        center: { lat: 30.469187, lng: 105.5646, alt: 930.2, heading: 2.1, pitch: -55.2 },
    },
    globe: {
        depthTestAgainstTerrain: true,
    },
})

// 地图实例
const marsMap = ref()
provide('marsMap', marsMap)
// 模型实例
const graphicLayer_m = ref()
provide('graphicLayer_m', graphicLayer_m)
// 起始点
const graphicLayer_s = ref()
provide('graphicLayer_s', graphicLayer_s)
// 航点实例
const graphicLayer_w = ref()
provide('graphicLayer_w', graphicLayer_w)
// 高度线实例
const graphicLayer_h = ref()
provide('graphicLayer_h', graphicLayer_h)
// 航线实例
const graphicLayer_l = ref()
provide('graphicLayer_l', graphicLayer_l)
// 四凌锥体视角实例
const graphicLayer_p = ref()
provide('graphicLayer_p', graphicLayer_p)

// 地图加载
const marsOnload = (map: mars3d.Map) => {
    marsMap.value = map
    graphicLayer_m.value = new mars3d.layer.GraphicLayer()
    graphicLayer_s.value = new mars3d.layer.GraphicLayer()
    graphicLayer_w.value = new mars3d.layer.GraphicLayer()
    graphicLayer_h.value = new mars3d.layer.GraphicLayer()
    graphicLayer_l.value = new mars3d.layer.GraphicLayer()
    graphicLayer_p.value = new mars3d.layer.GraphicLayer()
    graphicLayer_t.value = new mars3d.layer.GraphicLayer()
    graphicLayer_d.value = new mars3d.layer.GraphicLayer()
    marsMap.value.addLayer(graphicLayer_m.value)
    marsMap.value.addLayer(graphicLayer_s.value)
    marsMap.value.addLayer(graphicLayer_w.value)
    marsMap.value.addLayer(graphicLayer_h.value)
    marsMap.value.addLayer(graphicLayer_l.value)
    marsMap.value.addLayer(graphicLayer_p.value)
    marsMap.value.addLayer(graphicLayer_t.value)
    marsMap.value.addLayer(graphicLayer_d.value)
}

// 机场设置
const aerodromeSetting = ref(false)
provide('aerodromeSetting', aerodromeSetting)
// 当前选择机场
const aerodromeInfo = ref({})
provide('aerodromeInfo', aerodromeInfo)

// 当前选择标签
const activeTab = ref(0)
provide('activeTab', activeTab)

// 打开任务弹窗
const openAssignmentPop = ref(false)
provide('openAssignmentPop', openAssignmentPop)

// 打开添加航线库弹窗
const openAddShipLanePop = ref(false)
provide('openAddShipLanePop', openAddShipLanePop)

// 打开航线配置弹窗
const openShipLanesOptionsPop = ref('')
provide('openShipLanesOptionsPop', openShipLanesOptionsPop)

// 打开新建任务弹窗
const isShowAssignmentPop = ref(false)
provide('isShowAssignmentPop', isShowAssignmentPop)

// 重置地图
const resetMap = () => {
    graphicLayer_m.value.clear()
    graphicLayer_s.value.clear()
    graphicLayer_w.value.clear()
    graphicLayer_h.value.clear()
    graphicLayer_l.value.clear()
    graphicLayer_p.value.clear()
    graphicLayer_t.value.clear()
}

const shipLanesStore = useShipLanes()
const { activeShipLanes } = storeToRefs(shipLanesStore)
// 是否编辑航点
const isEditWaypoint = computed(() => shipLanesStore.isEditWaypoint)
watch(openShipLanesOptionsPop, (newVal: string) => {
    if (!newVal) {
        shipLanesStore.changeWaypointEdit(false)
        shipLanesStore.resetShipForm()
        shipLanesStore.closeShipLanes()
        resetMap()
    }
})

const route = useRoute()

const mqttStore = useMqttStore()
const { isExecutingTask, deviceOsds, droneOsds } = storeToRefs(mqttStore)

onMounted(async () => {
    console.log('设备数据', deviceOsds.value)
    console.log('无人机数据', droneOsds.value)

    await projectStore.getProjectList()
    if (route.query.id) {
        const data = projectStore.projectList.filter((item: any) => item.id == route.query.id)[0]
        if (data) {
            currentProject.value = data
            isShowProject.value = false
            isShowLeft.value = true
            marsMap.value.flyToPoint([data.longitude, data.latitude, 500])
        }
    }

    console.log('是否正在执行任务', isExecutingTask.value)

    // 是否有正在执行的任务
    if (isExecutingTask.value) {
        mapStore.createRoute()
        openShipLanesOptionsPop.value = 'look'
        activeShipLanes.value = '1'
    }
})

// 退出路由清空数据
onUnmounted(() => {
    // 重置航线表单
    shipLanesStore.resetShipForm()
    // 重置地图
    resetMap()
    // 清空设备点位
    graphicLayer_d.value.clear()
    // 重置项目
    projectStore.reset()
    // 删除航线
    mapStore.deleteRoute()
    // 关闭直播
    if (isShowDroneLive.value) {
        isShowDroneLive.value = false
    }
    if (isShowCabinLive.value) {
        isShowCabinLive.value = false
    }
})
</script>

<style>
.triangle-container {
    position: relative;
    width: 100px; /* 根据需要调整容器的宽度 */
    margin: 0 auto;
}

.triangle {
    width: 0;
    height: 0;
    border-left: 18px solid transparent;
    border-right: 18px solid transparent;
    border-top: 18px solid #3388ff; /* 倒三角形的颜色 */
    position: absolute;
    bottom: 0px; /* 根据需要调整倒三角形的位置 */
    left: 0px;
    transform: translateX(-50%);
}

.text {
    text-align: center;
    color: #fff; /* 文字颜色 */
    font-size: 10px; /* 文字大小 */
    background-color: #ffffff00; /* 文字背景颜色 */
    position: relative;
    left: -50px;
    bottom: 5px;
    z-index: 1; /* 确保文字在倒三角形之上 */
}
</style>

<style scoped lang="scss">
.flight-space {
    position: relative;
    height: 100vh;
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
