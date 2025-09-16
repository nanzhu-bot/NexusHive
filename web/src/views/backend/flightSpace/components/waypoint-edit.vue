<template>
    <div class="waypoint-edit">
        <div class="waypoint-edit-options">
            <div v-for="option in options" :key="option.label" class="options-item" @click="addAction(option.action)">
                <img :src="option.icon" alt="" style="width: 24px; height: 24px" />
                <div class="options-item-text">{{ option.label }}</div>
            </div>
        </div>
        <div class="waypoint-edit-header">
            <div>航点编辑</div>
            <div style="cursor: pointer">
                <el-icon size="20" @click="shipLanesStore.changeWaypointEdit(false)"><Close /></el-icon>
            </div>
        </div>
        <div class="waypoint-edit-tabs">
            <div class="tabs-title">{{ shipLanesStore.currentPlacemark == 0 ? '起飞点' : '航点' + shipLanesStore.currentPlacemark }}</div>
            <div class="tabs-box">
                <el-tooltip
                    v-for="(item, index) in shipLanesAction"
                    :key="index"
                    effect="dark"
                    :content="actionData[item.actionActuatorFunc].title"
                    placement="top"
                >
                    <div class="tabs-box-item" :class="{ active: currentAction === index }" @click="currentAction = index">
                        <img :src="actionData[item.actionActuatorFunc].icon" alt="" style="width: 16px; height: 16px" />
                    </div>
                </el-tooltip>
            </div>
        </div>
        <div class="waypoint-edit-content" v-if="shipLanesAction.length > 0">
            <component
                :is="actionData[shipLanesAction[currentAction].actionActuatorFunc].component"
                v-model="shipLanesAction[currentAction].actionActuatorFuncParam"
            />
        </div>
        <div class="waypoint-edit-footer" v-if="shipLanesAction.length > 0">
            <el-popconfirm title="确定要删除吗？" placement="bottom" @confirm="delAction" :hide-after="0">
                <template #reference>
                    <el-button type="danger" :icon="Delete" circle />
                </template>
            </el-popconfirm>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useShipLanes } from '/@/stores/shipLanes'
import { Close, Delete } from '@element-plus/icons-vue'
import takePhoto from './shipActions/takePhoto.vue'
import startRecord from './shipActions/startRecord.vue'
import stopRecord from './shipActions/stopRecord.vue'
import hover from './shipActions/hover.vue'
import rotateYaw from './shipActions/rotateYaw.vue'
import gimbalRotate from './shipActions/gimbalRotate.vue'
import zoom from './shipActions/zoom.vue'
const shipLanesStore = useShipLanes()

// 所有动作参数
const actionActuatorFuncParams = computed(() => shipLanesStore.actionActuatorFuncParams)

// 当前选中的动作
const { currentAction } = storeToRefs(shipLanesStore)

// 获取当前航点动作
const shipLanesAction = computed(() => shipLanesStore.getCurrentPlacemarkAction)

// 动作列表
const actionData = {
    takePhoto: {
        icon: '/img/waypoint/icon9.png',
        title: '拍照',
        component: takePhoto,
    },
    startRecord: {
        icon: '/img/waypoint/icon1.png',
        title: '开始录像',
        component: startRecord,
    },
    stopRecord: {
        icon: '/img/waypoint/icon2.png',
        title: '停止录像',
        component: stopRecord,
    },
    hover: {
        icon: '/img/waypoint/icon6.png',
        title: '悬停',
        component: hover,
    },
    rotateYaw: {
        icon: '/img/waypoint/icon7.png',
        title: '飞行器偏航角',
        component: rotateYaw,
    },
    gimbalRotate: {
        icon: '/img/waypoint/icon8.png',
        title: '云台俯仰角',
        component: gimbalRotate,
    },
    zoom: {
        icon: '/img/waypoint/icon10.png',
        title: '相机变焦',
        component: zoom,
    },
}

// 添加动作列表
const options = [
    {
        label: '拍照',
        icon: actionData.takePhoto.icon,
        action: 'takePhoto',
    },
    {
        label: '开始录像',
        icon: actionData.startRecord.icon,
        action: 'startRecord',
    },
    {
        label: '停止录像',
        icon: actionData.stopRecord.icon,
        action: 'stopRecord',
    },
    {
        label: '悬停',
        icon: actionData.hover.icon,
        action: 'hover',
    },
    {
        label: '飞行器偏航角',
        icon: actionData.rotateYaw.icon,
        action: 'rotateYaw',
    },
    {
        label: '云台俯仰角',
        icon: actionData.gimbalRotate.icon,
        action: 'gimbalRotate',
    },
    {
        label: '相机变焦',
        icon: actionData.zoom.icon,
        action: 'zoom',
    },
]

// 添加动作
const addAction = (action: string) => {
    shipLanesStore.addAction(action)
    currentAction.value = shipLanesAction.value.length - 1
}

// 删除动作
const delAction = () => {
    shipLanesStore.deleteAction(currentAction.value)
    if (currentAction.value > 0) {
        currentAction.value -= 1
    } else {
        currentAction.value = 0
    }
}
</script>

<style scoped lang="scss">
.waypoint-edit {
    position: absolute;
    right: 10px;
    top: 10px;
    bottom: 10px;
    width: 360px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 99;
    background-color: #fff;
    border-radius: 12px;
    padding: 16px;

    .waypoint-edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 12px;
    }

    .waypoint-edit-options {
        position: absolute;
        right: 380px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        gap: 10px;

        .options-item {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
            background-color: #00000080;
            transition: all 0.3s ease;

            .options-item-text {
                font-size: 14px;
                font-weight: bold;
                color: #fff;
                white-space: nowrap;
            }
        }
    }

    .waypoint-edit-tabs {
        display: flex;
        font-size: 14px;
        font-weight: bold;
        color: #000000;
        padding-bottom: 12px;

        .tabs-title {
            height: 38px;
            line-height: 38px;
            text-align: center;
            margin-right: 10px;
        }

        .tabs-box {
            flex: 1;
            display: flex;
            flex-wrap: wrap;
            padding: 5px;
            background: #3c3c3c;
            border-radius: 5px;
            gap: 5px;

            .tabs-box-item {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 2px;
                cursor: pointer;

                &:hover {
                    background-color: #5cadff;
                }

                &.active {
                    background-color: #2d8cf0;
                }
            }
        }
    }

    .waypoint-edit-content {
    }

    .waypoint-edit-footer {
        display: flex;
        justify-content: flex-end;
    }
}
</style>
