import { defineStore } from 'pinia'
import type { shipForm, actionActuatorFuncParam, Placemark, actionGroup, action } from '/@/views/backend/flightSpace/type/shipLanes-type'
import toTemplateKml from '/@/utils/toTemplateKml'
import { uuid } from '/@/utils/random'
import { fileUpload } from '/@/api/common'
import toWayLinesWpml from '/@/utils/toWayLinesWpml'
import { addHx, editHx } from '/@/config/flyApi'
import { ElMessage } from 'element-plus'

interface Waypoint {
    index: number
    title: string
}

interface subForm {
    id: string | number
    name: string
    project_id: string | number
    scene: number | string
    type: string
    template: string
    wayline: string
    kmz: string
    kmz_json: any
    total_point: number
    // 航线文件夹id
    airline_floder_id: string | number
    // 预计里程
    mileage: number
    // 预计时间
    execution_time: string
    // 航点数量
    point_num: number
}

export const useShipLanes = defineStore('shipLanes', {
    state: () => {
        return {
            activeShipLanes: '',
            // 起飞点
            shipStartPoint: [] as any[],
            shipLanes: [] as any[],
            // 浏览
            browseShipLanes: false,
            // 当前选中的航线
            currentAction: 0,
            shipForm: {
                missionConfig: {
                    flyToWaylineMode: 'safely',
                    takeOffSecurityHeight: 20,
                    finishAction: 'goHome',
                    exitOnRCLost: 'goContinue',
                    executeRCLostAction: 'hover',
                    globalTransitionalSpeed: 15,
                    globalRTHHeight: 100,
                    takeOffRefPoint: '',
                    droneInfo: {
                        droneEnumValue: '91',
                        droneSubEnumValue: '0',
                    },
                    waylineAvoidLimitAreaMode: 0,
                    payloadInfo: {
                        payloadEnumValue: '80',
                        payloadSubEnumValue: '0',
                        payloadPositionIndex: '0',
                    },
                    autoRerouteInfo: {
                        missionAutoRerouteMode: 0,
                        transitionalAutoRerouteMode: 0,
                    },
                },
                Folder: {
                    templateType: 'waypoint',
                    templateId: 0,
                    waylineId: 0,
                    executeHeightMode: 'WGS84',
                    globalWaypointTurnMode: 'toPointAndStopWithDiscontinuityCurvature',
                    waylineCoordinateSysParam: {
                        heightMode: 'EGM96',
                        coordinateMode: 'WGS84',
                    },
                    globalHeight: 400,
                    autoFlightSpeed: 10,
                    globalWaypointHeadingParam: {
                        waypointHeadingMode: 'followWayline',
                        waypointHeadingPathMode: 'clockwise',
                    },
                    gimbalPitchMode: 'manual',
                    payloadParam: {
                        payloadPositionIndex: '0',
                        imageFormat: ['wide', 'zoom', 'ir'],
                    },
                    globalUseStraightLine: 1,
                    Placemark: [],
                },
            } as shipForm,
            graphicList: [],
            // 航点列表
            waypointList: [] as Waypoint[],
            // 当前编辑的航点
            currentWaypoint: null as Waypoint | null,
            // 是否编辑航点
            isEditWaypoint: false,
            // 航点信息
            placemark: {
                Point: {
                    coordinates: '',
                },
                index: 0,
                executeHeight: 400,
                waypointSpeed: 15,
                useGlobalHeight: 1,
                useGlobalSpeed: 1,
                useGlobalHeading: 1,
                useGlobalTurnParam: 1,
                useStraightLine: 1,
                actionGroup: {},
                gimbalPitchAngle: 0,
                isRisky: 0,
            } as Placemark,
            // 航点信息数据
            placemarkData: [] as Placemark[],
            // 航线动作参数
            actionActuatorFuncParams: {
                // 单拍动作
                takePhoto: {
                    payloadPositionIndex: '0',
                    fileSuffix: '',
                    payloadLensIndex: ['wide', 'zoom', 'ir'],
                    useGlobalPayloadLensIndex: 1,
                },
                // 悬停等待
                hover: {
                    hoverTime: 10,
                },
                // 开始录像
                startRecord: {
                    payloadPositionIndex: '0',
                    fileSuffix: '',
                    payloadLensIndex: ['wide', 'zoom', 'ir'],
                    useGlobalPayloadLensIndex: 1,
                },
                // 结束录像
                stopRecord: {
                    payloadPositionIndex: '0',
                    payloadLensIndex: ['wide', 'zoom', 'ir'],
                },
                // 对焦
                focus: {
                    payloadPositionIndex: '0',
                    isPointFocus: 0,
                    focusRegionWidth: 1,
                    focusRegionHeight: 1,
                    isInfiniteFocus: 0,
                },
                // 变焦
                zoom: {
                    payloadPositionIndex: '0',
                    focalLength: 120,
                },
                // 旋转云台
                gimbalRotate: {
                    payloadPositionIndex: '0',
                    gimbalHeadingYawBase: 'north',
                    gimbalRotateMode: 'absoluteAngle',
                    gimbalPitchRotateEnable: 1,
                    gimbalPitchRotateAngle: 0,
                    gimbalRollRotateEnable: 0,
                    gimbalRollRotateAngle: 0,
                    gimbalYawRotateEnable: 0,
                    gimbalYawRotateAngle: 0,
                    gimbalRotateTimeEnable: 0,
                    gimbalRotateTime: 2,
                },
                // 飞行器偏航
                rotateYaw: {
                    aircraftHeading: 0,
                    aircraftPathMode: 'clockwise',
                },
            } as actionActuatorFuncParam,
            actionGroup: {
                actionGroupId: 0,
                actionGroupStartIndex: 0,
                actionGroupEndIndex: 0,
                actionGroupMode: 'sequence',
                actionTrigger: {
                    actionTriggerType: 'reachPoint',
                },
                action: [] as action[],
            } as actionGroup,
            // 当前编辑的航点索引
            currentPlacemark: 0,
            // 保存航线
            subForm: {
                id: '',
                name: '',
                project_id: '',
                scene: '0',
                type: '0',
                template: '',
                wayline: '',
                kmz: '',
                kmz_json: {} as any,
                total_point: 0,
                // 航线文件夹id
                airline_floder_id: '',
                // 航点数量
                point_num: 0,
                // 预计里程
                mileage: 0,
                // 预计时间
                execution_time: '',
            } as subForm,
        }
    },
    actions: {
        // 重置
        resetShipForm() {
            this.$reset()
        },
        // 回显
        showShipForm(kmzJson: any) {
            this.shipForm = kmzJson
            this.placemarkData = kmzJson.Folder.Placemark
            this.shipStartPoint = kmzJson.shipStartPoint
            this.shipLanes = kmzJson.shipLanes
        },
        // 添加航点
        addPlacemark(coordinates: string, isStartPoint: boolean) {
            if (isStartPoint) {
                const arr = this.shipStartPoint[0]
                const arr1 = [arr[1], arr[0], arr[2]]
                this.shipForm.missionConfig.takeOffRefPoint = arr1.join(',')
            } else {
                const placemark = JSON.parse(JSON.stringify(this.placemark))
                const actionGroup = JSON.parse(JSON.stringify(this.actionGroup))
                placemark.Point.coordinates = coordinates
                placemark.index = isStartPoint ? 0 : this.placemarkData.length
                actionGroup.actionGroupId = isStartPoint ? 0 : this.placemarkData.length
                actionGroup.actionGroupStartIndex = this.placemarkData.length
                actionGroup.actionGroupEndIndex = this.placemarkData.length
                actionGroup.action.push({
                    actionId: 0,
                    actionActuatorFunc: 'rotateYaw',
                    actionActuatorFuncParam: this.actionActuatorFuncParams.rotateYaw,
                })
                actionGroup.action.push({
                    actionId: 1,
                    actionActuatorFunc: 'gimbalRotate',
                    actionActuatorFuncParam: this.actionActuatorFuncParams.gimbalRotate,
                })
                actionGroup.action.push({
                    actionId: 2,
                    actionActuatorFunc: 'zoom',
                    actionActuatorFuncParam: this.actionActuatorFuncParams.zoom,
                })
                placemark.actionGroup = actionGroup
                placemark.executeHeight = this.shipForm.Folder.globalHeight
                this.placemarkData.push(placemark)
            }
            this.resetId()
        },
        // 删除航点
        deletePlacemark(index: number) {
            this.placemarkData.splice(index, 1)
            this.resetId()
        },
        // 重新计算id
        resetId() {
            this.placemarkData.forEach((item, index) => {
                item.index = index
            })
        },
        // 添加动作
        addAction(action: string) {
            this.placemarkData[this.currentPlacemark].actionGroup.action.push({
                actionId: this.placemarkData[this.currentPlacemark].actionGroup.action.length,
                actionActuatorFunc: action,
                actionActuatorFuncParam: this.actionActuatorFuncParams[action as keyof typeof this.actionActuatorFuncParams],
            })
        },
        // 删除动作
        deleteAction(index: number) {
            this.placemarkData[this.currentPlacemark].actionGroup.action.splice(index, 1)
            this.resetActionId()
        },
        // 重新计算actionId
        resetActionId() {
            this.placemarkData[this.currentPlacemark].actionGroup.action.forEach((item, index) => {
                item.actionId = index
            })
        },
        // 保存
        async savePlacemark() {
            this.shipForm.Folder.Placemark = this.placemarkData
            const mData = toTemplateKml(this.shipForm)
            const blob = new Blob([mData], { type: 'application/xml' }) // 创建 Blob 对象
            const temFile = new File([blob], 'template.kml', { type: 'application/xml' }) // 创建 File 对象
            const formData = new FormData()
            formData.append('file', temFile)
            const res = await fileUpload(formData, { uuid: this.generateUuid() })
            if (res.code == 1) {
                this.subForm.template = res.data.file.url
            }

            const wData = toWayLinesWpml(this.shipForm)
            const wBlob = new Blob([wData], { type: 'application/xml' })
            const wFile = new File([wBlob], 'wayline.wpml', { type: 'application/xml' })
            const wFormData = new FormData()
            wFormData.append('file', wFile)
            const wRes = await fileUpload(wFormData, { uuid: this.generateUuid() })
            if (wRes.code == 1) {
                this.subForm.wayline = wRes.data.file.url
            }

            // 预计里程
            this.subForm.mileage = parseFloat(this.subForm.mileage.toFixed(2))

            // 航点
            this.subForm.point_num = this.shipLanes.length

            // 预计时间
            this.subForm.execution_time = this.getExecutionTime

            // 航线高度
            this.shipForm.Folder.Placemark.forEach((item) => {
                item.executeHeight = this.shipForm.Folder.globalHeight
            })

            this.subForm.kmz_json = {
                ...this.shipForm,
                shipStartPoint: this.shipStartPoint,
                shipLanes: this.shipLanes,
            }
            this.subForm.total_point = this.shipStartPoint.length + this.shipLanes.length

            console.log('航线数据', this.subForm)
            if (this.subForm.id) {
                const hxRes = await editHx(this.subForm)
                if (hxRes.code == 1) {
                    ElMessage.success('编辑成功')
                    return true
                }
            } else {
                const hxRes = await addHx(this.subForm)
                if (hxRes.code == 1) {
                    ElMessage.success('添加成功')
                    return true
                }
            }
            return false
        },
        // 保存当前航点
        saveCurrentPlacemark(index: number) {
            this.currentPlacemark = index
        },
        // 生成uuid
        generateUuid() {
            const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
                const r = (Math.random() * 16) | 0
                const v = c === 'x' ? r : (r & 0x3) | 0x8
                return v.toString(16)
            })
            return uuid.replace(/-/g, '')
        },
        // 改变航点编辑状态
        changeWaypointEdit(bool: boolean) {
            this.isEditWaypoint = bool
        },
        // 添加起飞点
        addStartPoint(point: any) {
            this.shipStartPoint.push(point)
        },
        // 添加航点
        addWaypoint(point: any) {
            this.shipLanes.push(point)
        },
        // 关闭航线
        closeShipLanes() {
            this.shipLanes = []
            this.graphicList = []
            this.shipStartPoint = []
            this.waypointList = []
        },
        // 改变高度模型
        changeHeightModel() {
            this.graphicList.forEach((item: any) => {
                if (item.attr.type === 1 || item.attr.type === 4) {
                    if (item.attr.index === 0) {
                        item.position = this.newStartPoint[0]
                    } else {
                        item.position = this.newShipLanes[item.attr.index - 1]
                    }
                    if (item.attr.type === 4) {
                        item.setStyle({
                            html: `<div class="triangle-container">
                                        <div class="triangle"></div>
                                        <div class="text">${item.attr.index}</div>
                                    </div>`,
                        })
                    }
                }
                if (item.attr.type === 2) {
                    if (item.attr.index === 1) {
                        item.positions = [this.newStartPoint[0], this.newShipLanes[item.attr.index - 1]]
                    } else {
                        item.positions = [this.newShipLanes[item.attr.index - 2], this.newShipLanes[item.attr.index - 1]]
                    }
                }
                if (item.attr.type === 3) {
                    if (item.attr.index === 0) {
                        item.positions = [this.shipStartPoint[0], this.newStartPoint[0]]
                    } else {
                        item.positions = [this.shipLanes[item.attr.index - 1], this.newShipLanes[item.attr.index - 1]]
                    }
                }
            })
        },
        deleteWaypoint(index: number, map: any) {
            if (index === 0) {
                this.shipStartPoint.splice(index, 1)
                const deleteList = this.graphicList.filter((item: any) => item.attr.index === index)
                deleteList.forEach((item: any) => {
                    if (item.id === 'model') {
                        item.attr.index = index + 1
                    } else {
                        map.removeLayer(item)
                    }
                })
                this.graphicList = this.graphicList.filter((item: any) => item.attr.index !== index)
                this.waypointList = this.waypointList.filter((item: any) => item.index !== index)
                this.changeHeightModel()
            } else {
                this.shipLanes.splice(index - 1, 1)
                const deleteList = this.graphicList.filter((item: any) => item.attr.index === index)
                deleteList.forEach((item: any) => {
                    if (item.id === 'model') {
                        item.attr.index = index - 1
                    } else {
                        map.removeLayer(item)
                    }
                })
                this.graphicList = this.graphicList.filter((item: any) => item.attr.index !== index)
                this.graphicList.forEach((item: any) => {
                    if (item.attr.index > index) {
                        item.attr.index = item.attr.index - 1
                    }
                })
                this.waypointList = this.waypointList.filter((item: any) => item.index !== index)
                this.changeHeightModel()
            }
        },
    },
    getters: {
        newShipLanes: (state) => {
            state.shipForm.Folder.globalHeight = Number(state.shipForm.Folder.globalHeight)
            state.shipForm.Folder.Placemark.forEach((item) => {
                item.executeHeight = Number(item.executeHeight)
            })
            state.shipForm.missionConfig.takeOffSecurityHeight = Number(state.shipForm.missionConfig.takeOffSecurityHeight)
            const arr = state.shipLanes.map((item) => {
                if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'EGM96') {
                    return [item[0], item[1], state.shipForm.Folder.globalHeight]
                }
                if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'relativeToStartPoint') {
                    return [
                        item[0],
                        item[1],
                        (state.shipForm.Folder.globalHeight || 0) + (state.shipForm.missionConfig.takeOffSecurityHeight || 0) + parseFloat(item[2]),
                    ]
                }
                if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'aboveGroundLevel') {
                    return [item[0], item[1], (state.shipForm.Folder.globalHeight || 0) + parseFloat(item[2])]
                }
            })
            return arr
        },
        // 新起飞点
        newStartPoint: (state) => {
            state.shipForm.Folder.globalHeight = Number(state.shipForm.Folder.globalHeight)
            state.shipForm.Folder.Placemark.forEach((item) => {
                item.executeHeight = Number(item.executeHeight)
            })
            state.shipForm.missionConfig.takeOffSecurityHeight = Number(state.shipForm.missionConfig.takeOffSecurityHeight)
            return state.shipStartPoint.map((item) => {
                if (state.shipForm.missionConfig.flyToWaylineMode === 'safely') {
                    if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'EGM96') {
                        return [item[0], item[1], state.shipForm.Folder.globalHeight]
                    }
                    if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'relativeToStartPoint') {
                        console.log([
                            item[0],
                            item[1],
                            (state.shipForm.Folder.globalHeight || 0) +
                                (state.shipForm.missionConfig.takeOffSecurityHeight || 0) +
                                parseFloat(item[2]),
                        ])
                        return [
                            item[0],
                            item[1],
                            (state.shipForm.Folder.globalHeight || 0) +
                                (state.shipForm.missionConfig.takeOffSecurityHeight || 0) +
                                parseFloat(item[2]),
                        ]
                    }
                    if (state.shipForm.Folder.waylineCoordinateSysParam.heightMode === 'aboveGroundLevel') {
                        console.log([item[0], item[1], (state.shipForm.Folder.globalHeight || 0) + parseFloat(item[2])])
                        return [item[0], item[1], (state.shipForm.Folder.globalHeight || 0) + parseFloat(item[2])]
                    }
                }
                return [item[0], item[1], (state.shipForm.missionConfig.takeOffSecurityHeight || 0) + parseFloat(item[2])]
            })
        },
        // 获取当前航点动作
        getCurrentPlacemarkAction: (state) => {
            return state.placemarkData[state.currentPlacemark].actionGroup.action
        },
        // 预计时间
        getExecutionTime: (state) => {
            const minutes = Math.floor(state.subForm.mileage / state.placemark.waypointSpeed / 60)
            const remainingSeconds = (state.subForm.mileage / state.placemark.waypointSpeed) % 60
            return `${minutes}分${parseInt(remainingSeconds.toString())}秒`
        },
    },
})
