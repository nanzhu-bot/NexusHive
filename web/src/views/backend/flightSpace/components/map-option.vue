<!-- 只做地图的设置ts -->
<template>
    <div></div>
</template>

<script setup lang="ts">
import { ref, inject, watch, computed } from 'vue'
import * as mars3d from 'mars3d'
import { useShipLanes } from '/@/stores/shipLanes'
import { ElMessage } from 'element-plus'
import { storeToRefs } from 'pinia'

const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')

const marsMap = inject('marsMap')

const graphicLayer_m = inject('graphicLayer_m')
const graphicLayer_s = inject('graphicLayer_s')
const graphicLayer_h = inject('graphicLayer_h')
const graphicLayer_l = inject('graphicLayer_l')
const graphicLayer_p = inject('graphicLayer_p')
const graphicLayer_w = inject('graphicLayer_w')

const shipLanesStore = useShipLanes()

const { subForm } = storeToRefs(shipLanesStore)

// 当前选中的航线
const activeShipLanes = computed(() => shipLanesStore.activeShipLanes)
// 航点列表
const shipLanes = computed(() => shipLanesStore.shipLanes)
// 新航点列表
const newShipLanes = computed(() => shipLanesStore.newShipLanes)
// 图形列表
const graphicList = computed(() => shipLanesStore.graphicList)
// 飞行器配置
const form = computed(() => shipLanesStore.shipForm)
// 起飞点
const shipStartPoint = computed(() => shipLanesStore.shipStartPoint)
// 新起飞点
const newStartPoint = computed(() => shipLanesStore.newStartPoint)
// 当前编辑的航点
const currentWaypoint = computed(() => shipLanesStore.currentWaypoint)
// 是否编辑航点
const isEditWaypoint = computed(() => shipLanesStore.isEditWaypoint)

// 监听航点高度模式变化
watch(
    () => form.value.Folder.waylineCoordinateSysParam.heightMode,
    (newVal) => {
        shipLanesStore.changeHeightModel()
    }
)
// 监听飞行器爬升模式变化
watch(
    () => form.value.missionConfig.flyToWaylineMode,
    (newVal) => {
        shipLanesStore.changeHeightModel()
    }
)
// 监听航点高度变化
watch(
    () => form.value.Folder.globalHeight,
    (newVal) => {
        if (newVal < 0) {
            form.value.Folder.globalHeight = 0
            ElMessage.warning('航点高度不能小于0')
            return
        }
        shipLanesStore.changeHeightModel()
    }
)
// 监听安全高度变化
watch(
    () => form.value.missionConfig.takeOffSecurityHeight,
    (newVal) => {
        if (newVal < 0) {
            form.value.missionConfig.takeOffSecurityHeight = 0
            ElMessage.warning('安全高度不能小于0')
            return
        }
        shipLanesStore.changeHeightModel()
    }
)

watch(openShipLanesOptionsPop, (newVal: string) => {
    if (['add', 'edit'].includes(newVal)) {
        addContextMenu()
    } else {
        marsMap.value.unbindContextMenu()
    }
})

watch(activeShipLanes, (newVal: string) => {
    if (newVal) {
        addStartPointModel(0)
        addHeightLine(0, 'start')
        // 添加航点
        shipLanes.value.forEach((item, index) => {
            addWaypointModel(index + 1)
            if (newShipLanes.value.length > 0) {
                addRoute(index + 1)
            }
            // 如果是修改就添加高度线和模型
            if (openShipLanesOptionsPop.value === 'edit') {
                console.log('edit')
                addModel(shipLanes.value.length)
                if (newShipLanes.value.length > 0) {
                    addHeightLine(index + 1)
                }
            }
        })
    }
})

// 创建右键菜单
const contextMenu = [
    {
        text: '设置起始点',
        callback: function (e) {
            addStartPoint(e.cartesian)
        },
    },
    {
        text: '添加航点',
        callback: function (e) {
            console.log(e)
            addWaypoint(e.cartesian)
        },
    },
]

// 添加起始点
const addStartPoint = (cartesian: any) => {
    if (shipLanesStore.shipStartPoint.length > 0) {
        return ElMessage.warning('已有起始点')
    }
    const point = mars3d.LngLatPoint.fromCartesian(cartesian)
    shipLanesStore.addStartPoint([point.lng, point.lat, point.alt])
    addModel(shipStartPoint.value.length - 1)
    addStartPointModel(shipStartPoint.value.length - 1)
    addHeightLine(shipStartPoint.value.length - 1, 'start')
    if (newShipLanes.value.length > 0) {
        addRoute(1)
    }
    // 添加航点
    shipLanesStore.addPlacemark(point.lng + ',' + point.lat, true)
}

// 添加航点
const addWaypoint = (cartesian: any) => {
    if (shipLanesStore.shipStartPoint.length === 0) {
        return ElMessage.warning('请先添加起始点')
    }
    const point = mars3d.LngLatPoint.fromCartesian(cartesian)
    shipLanesStore.addWaypoint([point.lng, point.lat, point.alt])
    addModel(newShipLanes.value.length)
    addWaypointModel(newShipLanes.value.length)
    addHeightLine(newShipLanes.value.length)
    if (newShipLanes.value.length > 0) {
        addRoute(newShipLanes.value.length)
    }
    // 添加航点
    shipLanesStore.addPlacemark(point.lng + ',' + point.lat, false)
}

// 添加模型
const addModel = (index: number) => {
    const model = graphicLayer_m.value.getGraphics()[0]
    if (model) {
        if (index === 0) {
            model.position = newStartPoint.value[index]
        } else {
            model.position = newShipLanes.value[index - 1]
        }
        model.attr.index = index
        return
    }
    const graphicModel = new mars3d.graphic.ModelEntity({
        id: 'model',
        position: newStartPoint.value[index],
        style: {
            url: '/model/dajiang.gltf',
            scale: 1,
            minimumPixelSize: 100,
            pitch: 0, // 固定角度
        },
        attr: { index, type: 1 },
    })
    graphicLayer_m.value.addGraphic(graphicModel)
    graphicList.value.push(graphicModel)
}

// 添加起飞点
const addStartPointModel = (index: number) => {
    const divGraphic = new mars3d.graphic.DivGraphic({
        position: newStartPoint.value[index],
        style: {
            html: `<div class="triangle-container">
                        <div class="triangle"></div>
                        <div class="text">起</div>
                    </div>`,
        },
        attr: { index, type: 1 },
    })
    graphicLayer_s.value.addGraphic(divGraphic)
    graphicList.value.push(divGraphic)

    if (!['add', 'edit'].includes(openShipLanesOptionsPop.value)) {
        return
    }

    divGraphic.on('click', (e) => {
        editWaypoint(e.graphic.options.attr.index)
    })

    // 添加鼠标右键操作
    divGraphic.bindContextMenu([
        {
            text: '编辑航点',
            icon: 'fa fa-edit',
            callback: function (e) {
                editWaypoint(e.graphic.options.attr.index)
            },
        },
        {
            text: '删除起始点',
            icon: 'fa fa-trash',
            callback: function (e) {
                deleteWaypoint(e.graphic.options.attr.index)
                shipLanesStore.deletePlacemark(e.graphic.options.attr.index)
            },
        },
    ])
}

// 添加航点
const addWaypointModel = (index: number) => {
    const divGraphic = new mars3d.graphic.DivGraphic({
        position: newShipLanes.value[index - 1],
        style: {
            html: `<div class="triangle-container">
                        <div class="triangle"></div>
                        <div class="text">${index}</div>
                    </div>`,
        },
        attr: { index, type: 4 },
    })
    graphicLayer_w.value.addGraphic(divGraphic)
    graphicList.value.push(divGraphic)

    if (!['add', 'edit'].includes(openShipLanesOptionsPop.value)) {
        return
    }

    divGraphic.on('click', (e) => {
        editWaypoint(e.graphic.options.attr.index)
    })

    // 添加鼠标右键操作
    divGraphic.bindContextMenu([
        {
            text: '编辑航点',
            icon: 'fa fa-edit',
            callback: function (e) {
                editWaypoint(e.graphic.options.attr.index)
            },
        },
        {
            text: '删除航点',
            icon: 'fa fa-trash',
            callback: function (e) {
                deleteWaypoint(e.graphic.options.attr.index)
                shipLanesStore.deletePlacemark(e.graphic.options.attr.index)
            },
        },
    ])
}

// 删除航点
const deleteWaypoint = (index: number) => {
    shipLanesStore.deleteWaypoint(index, marsMap.value)
    shipLanesStore.currentAction = 0
}

// 编辑航点
const editWaypoint = (index: number) => {
    if (index === 0) {
        return ElMessage.warning('起始点没有动作')
    }
    const model = graphicLayer_m.value.getGraphics()[0]
    if (model) {
        if (index === 0) {
            model.position = newStartPoint.value[index]
        } else {
            model.position = newShipLanes.value[index - 1]
        }
        model.attr.index = index
    }
    shipLanesStore.saveCurrentPlacemark(index - 1)
    shipLanesStore.changeWaypointEdit(true)
    shipLanesStore.currentAction = 0
}

// 添加航线
const addRoute = (index) => {
    let positions = [newShipLanes.value[index - 2], newShipLanes.value[index - 1]]
    if (index === 1) {
        positions = [newStartPoint.value[0], newShipLanes.value[0]]
    }
    console.log(positions)
    // 计算两点间的距离（米）
    const distance = mars3d.MeasureUtil.getDistance(positions)
    subForm.value.mileage += parseFloat(distance.toFixed(2))

    const graphic = new mars3d.graphic.PolylineEntity({
        positions: positions,
        style: {
            width: 5,
            color: '#3388ff',
        },
        attr: { index, type: 2 },
    })
    graphicLayer_l.value.addGraphic(graphic)
    graphicList.value.push(graphic)
}

// 添加高度线
const addHeightLine = (index: number, type: string = '') => {
    if (type === 'start') {
        const startPos = shipStartPoint.value[index]
        const endPos = newStartPoint.value[index]
        const distance = mars3d.MeasureUtil.getDistance([startPos, endPos])
        subForm.value.mileage += parseFloat(distance.toFixed(2))
        const graphic = new mars3d.graphic.PolylineEntity({
            positions: [startPos, endPos],
            style: {
                width: 5,
                color: '#3388ff',
                materialType: '',
            },
            attr: { index, type: 3 },
        })
        graphicLayer_h.value.addGraphic(graphic)
        graphicList.value.push(graphic)
        return
    }
    let positions = [shipLanes.value[index - 1], newShipLanes.value[index - 1]]
    if (index === 0) {
        positions = [shipStartPoint.value[index], newStartPoint.value[index - 1]]
    }

    // 计算两点间的距离（米）
    const graphic = new mars3d.graphic.PolylineEntity({
        positions: positions,
        style: {
            materialType: 'LineDotDash',
            width: 3,
            color: '#ffffff',
        },
        attr: { index, type: 3 },
    })

    graphicLayer_h.value.addGraphic(graphic)
    graphicList.value.push(graphic)
}

// 添加四棱锥
const addTetrahedron = (point: any) => {}

// 添加地右键菜单
const addContextMenu = () => {
    marsMap.value.bindContextMenu(contextMenu)
}
</script>

<style scoped lang="scss"></style>
