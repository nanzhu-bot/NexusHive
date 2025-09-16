<template>
    <div class="ship-lanes-options">
        <div class="options-header">
            <div class="options-header-title">添加航线</div>
            <div class="options-header-right">
                <el-tooltip class="box-item" effect="dark" content="保存航线" placement="top">
                    <img src="/img/image/save.png" alt="保存" class="back-icon" @click="saveShipLanes" />
                </el-tooltip>
                <el-tooltip class="box-item" effect="dark" content="退出航线配置" placement="top">
                    <img src="/img/image/back.png" alt="退出" class="back-icon" @click="openShipLanesOptionsPop = ''" />
                </el-tooltip>
            </div>
        </div>
        <div class="options-content flex-col">
            <!-- 设置起飞点 -->
            <!-- <div class="content-item flex justify-between">
                <div class="content-item-text">已设置参考起飞点</div>
                <div class="content-item-text" style="cursor: pointer" @click="resetTakeOffPoint">重设起飞点</div>
            </div> -->
            <!-- 相机设置 -->
            <!-- <div class="content-item flex-col">
                <div class="content-item-text">相机设置</div>
                <div class="content-item-tabs">
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.payloadParam.imageFormat.includes('wide') }"
                        @click="
                            form.Folder.payloadParam.imageFormat.includes('wide')
                                ? form.Folder.payloadParam.imageFormat.splice(form.Folder.payloadParam.imageFormat.indexOf('wide'), 1)
                                : form.Folder.payloadParam.imageFormat.push('wide')
                        "
                    >
                        广角照片
                    </div>
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.payloadParam.imageFormat.includes('zoom') }"
                        @click="
                            form.Folder.payloadParam.imageFormat.includes('zoom')
                                ? form.Folder.payloadParam.imageFormat.splice(form.Folder.payloadParam.imageFormat.indexOf('zoom'), 1)
                                : form.Folder.payloadParam.imageFormat.push('zoom')
                        "
                    >
                        变焦照片
                    </div>
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.payloadParam.imageFormat.includes('ir') }"
                        @click="
                            form.Folder.payloadParam.imageFormat.includes('ir')
                                ? form.Folder.payloadParam.imageFormat.splice(form.Folder.payloadParam.imageFormat.indexOf('ir'), 1)
                                : form.Folder.payloadParam.imageFormat.push('ir')
                        "
                    >
                        红外照片
                    </div>
                </div>
            </div> -->
            <!-- 安全高度设置 -->
            <div class="content-item flex-col">
                <div class="content-item-tabs">
                    <div
                        class="tabs-item"
                        :class="{ active: form.missionConfig.flyToWaylineMode === 'safely' }"
                        @click="form.missionConfig.flyToWaylineMode = 'safely'"
                    >
                        垂直爬升
                    </div>
                    <div
                        class="tabs-item"
                        :class="{ active: form.missionConfig.flyToWaylineMode === 'pointToPoint' }"
                        @click="form.missionConfig.flyToWaylineMode = 'pointToPoint'"
                    >
                        倾斜爬升
                    </div>
                </div>
                <div class="content-item-bottom flex">
                    <img src="/img/image/pslx.png" alt="高度" class="bottom-icon" />
                    <div class="bottom-content flex-col">
                        <div class="bottom-content-item" @click="form.missionConfig.takeOffSecurityHeight += 100">+100</div>
                        <div class="bottom-content-item" @click="form.missionConfig.takeOffSecurityHeight += 10">+10</div>
                        <el-input v-model="form.missionConfig.takeOffSecurityHeight" :min="0" class="input-item bottom-content-item" />
                        <div class="bottom-content-item" @click="form.missionConfig.takeOffSecurityHeight -= 10">-10</div>
                        <div class="bottom-content-item" @click="form.missionConfig.takeOffSecurityHeight -= 100">-100</div>
                    </div>
                    <div class="flex align-center">m</div>
                </div>
            </div>
            <!-- 航点高度模式 -->
            <div class="content-item flex-col">
                <div class="content-item-text">航点高度模式</div>
                <div class="content-item-tabs">
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.waylineCoordinateSysParam.heightMode === 'EGM96' }"
                        @click="form.Folder.waylineCoordinateSysParam.heightMode = 'EGM96'"
                    >
                        绝对高度
                    </div>
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.waylineCoordinateSysParam.heightMode === 'relativeToStartPoint' }"
                        @click="form.Folder.waylineCoordinateSysParam.heightMode = 'relativeToStartPoint'"
                    >
                        相对起飞点高度
                    </div>
                    <div
                        class="tabs-item"
                        :class="{ active: form.Folder.waylineCoordinateSysParam.heightMode === 'aboveGroundLevel' }"
                        @click="form.Folder.waylineCoordinateSysParam.heightMode = 'aboveGroundLevel'"
                    >
                        相对地形高度
                    </div>
                </div>
                <div class="content-item-bottom flex">
                    <img
                        src="/img/image/jdgd.png"
                        alt="绝对高"
                        class="bottom-icon"
                        v-if="form.Folder.waylineCoordinateSysParam.heightMode === 'EGM96'"
                    />
                    <img
                        src="/img/image/xdqfg.png"
                        alt="相对起飞点高"
                        class="bottom-icon"
                        v-if="form.Folder.waylineCoordinateSysParam.heightMode === 'relativeToStartPoint'"
                    />
                    <img
                        src="/img/image/xddmg.png"
                        alt="相对地形高"
                        class="bottom-icon"
                        v-if="form.Folder.waylineCoordinateSysParam.heightMode === 'aboveGroundLevel'"
                    />
                    <div class="bottom-content flex-col">
                        <div class="bottom-content-item" @click="form.Folder.globalHeight += 100">+100</div>
                        <div class="bottom-content-item" @click="form.Folder.globalHeight += 10">+10</div>
                        <el-input v-model="form.Folder.globalHeight" :min="0" class="input-item bottom-content-item" />
                        <div class="bottom-content-item" @click="form.Folder.globalHeight -= 10">-10</div>
                        <div class="bottom-content-item" @click="form.Folder.globalHeight -= 100">-100</div>
                    </div>
                    <div class="flex align-center">m</div>
                </div>
            </div>
            <!-- 全局航线速度 -->
            <div class="content-item flex-col">
                <div class="content-item-text">全局航线速度</div>
                <div class="flex justify-between">
                    <div class="speed-item flex align-center justify-center" @click="form.Folder.autoFlightSpeed -= 1">
                        <el-icon><Minus /></el-icon>
                    </div>
                    <div class="speed-input flex-1 flex align-center justify-center">
                        <el-input v-model="form.Folder.autoFlightSpeed" class="flex-1" :min="0" />
                        <div class="speed-text flex-1">m/s</div>
                    </div>
                    <div class="speed-item flex align-center justify-center" @click="form.Folder.autoFlightSpeed += 1">
                        <el-icon><Plus /></el-icon>
                    </div>
                </div>
            </div>
            <!-- 高级设置 -->
            <div class="content-item">
                <el-collapse>
                    <el-collapse-item name="1">
                        <template #title="{ isActive }">
                            <div :class="['title-wrapper', { 'is-active': isActive }]">高级设置</div>
                        </template>
                        <div class="flex-col gap-10">
                            <!-- 全局航线速度 -->
                            <!-- <div class="flex-col gap-10">
                                <div class="content-item-text">全局航线速度</div>
                                <div class="flex justify-between">
                                    <div class="speed-item flex align-center justify-center" @click="form.Folder.autoFlightSpeed -= 1">
                                        <el-icon><Minus /></el-icon>
                                    </div>
                                    <div class="speed-input flex-1 flex align-center justify-center">
                                        <el-input v-model="form.Folder.autoFlightSpeed" class="flex-1" :min="0" />
                                        <div class="speed-text flex-1">m/s</div>
                                    </div>
                                    <div class="speed-item flex align-center justify-center" @click="form.Folder.autoFlightSpeed += 1">
                                        <el-icon><Plus /></el-icon>
                                    </div>
                                </div>
                            </div> -->
                            <!-- 航点类型 -->
                            <div class="flex-col gap-10">
                                <div class="content-item-text">航点类型</div>
                                <el-select v-model="form.Folder.globalWaypointTurnMode" placeholder="Select" style="width: 100%">
                                    <el-tooltip
                                        v-for="item in waypointType"
                                        :key="item.value"
                                        effect="dark"
                                        :content="item.description"
                                        placement="right"
                                        class="box-item"
                                    >
                                        <el-option :label="item.label" :value="item.value" />
                                    </el-tooltip>
                                </el-select>
                            </div>
                            <!-- 飞行器偏航角模式 -->
                            <div class="flex-col gap-10">
                                <div class="content-item-text">飞行器偏航角模式</div>
                                <el-select
                                    v-model="form.Folder.globalWaypointHeadingParam.waypointHeadingMode"
                                    placeholder="Select"
                                    style="width: 100%"
                                >
                                    <el-tooltip
                                        v-for="item in yawMode"
                                        :key="item.value"
                                        effect="dark"
                                        :content="item.description"
                                        placement="right"
                                        class="box-item"
                                    >
                                        <el-option :label="item.label" :value="item.value" />
                                    </el-tooltip>
                                </el-select>
                            </div>
                            <!-- 航点间云台俯仰角控制模式 -->
                            <div class="flex-col gap-10">
                                <div class="content-item-text">航点间云台俯仰角控制模式</div>
                                <el-select v-model="form.Folder.gimbalPitchMode" placeholder="Select" style="width: 100%">
                                    <el-tooltip
                                        v-for="item in waypointElevationMode"
                                        :key="item.value"
                                        effect="dark"
                                        :content="item.description"
                                        placement="right"
                                        class="box-item"
                                    >
                                        <el-option :label="item.label" :value="item.value" />
                                    </el-tooltip>
                                </el-select>
                            </div>
                            <!-- 完成动作 -->
                            <div class="flex-col gap-10">
                                <div class="content-item-text">完成动作</div>
                                <el-select v-model="form.missionConfig.finishAction" placeholder="Select" style="width: 100%">
                                    <el-tooltip
                                        v-for="item in waypointActionMode"
                                        :key="item.value"
                                        effect="dark"
                                        :content="item.description"
                                        placement="right"
                                        class="box-item"
                                    >
                                        <el-option :label="item.label" :value="item.value" />
                                    </el-tooltip>
                                </el-select>
                            </div>
                        </div>
                    </el-collapse-item>
                </el-collapse>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, inject, computed } from 'vue'
import { Minus, Plus } from '@element-plus/icons-vue'
import { useShipLanes } from '/@/stores/shipLanes'

// 打开航线配置弹窗
const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')

const shipLanesStore = useShipLanes()

const form = computed(() => shipLanesStore.shipForm)

// watch(
//     () => form.value.Folder.globalHeight,
//     (newVal) => {
//         console.log(newVal)
//     }
// )

// 航点类型
const waypointType = ref([
    {
        value: 'coordinateTurn',
        label: '协调转弯，不过点，提前转弯',
        description: '航线中，航点之间使用协调转弯，不过点，提前转弯',
    },
    {
        value: 'toPointAndPassWithContinuityCurvature',
        label: '曲线飞行，飞行器过点不停',
        description: '航线中，航点之间使用曲线飞行，飞行器过点不停',
    },
    {
        value: 'toPointAndStopWithDiscontinuityCurvature',
        label: '直线飞行，飞行器到点停',
        description: '航线中，航点之间使用直线飞行，飞行器到点停',
    },
    {
        value: 'toPointAndStopWithContinuityCurvature',
        label: '曲线飞行，飞行器到点停',
        description: '航线中，航点之间使用曲线飞行，飞行器到点停',
    },
])

// 飞行器偏航角模式
const yawMode = ref([
    {
        value: 'followWayline',
        label: '沿航线方向',
        description: '沿航线方向：飞行器机头沿着航线方向飞至下一航点。',
    },
    {
        value: 'manually',
        label: '手动控制',
        description: '手动控制：飞行器在飞至下一航点的过程中，用户可以手动控制飞行器机头朝向。',
    },
    {
        value: 'fixed',
        label: '锁定当前偏航角',
        description: '锁定当前偏航角：飞行器机头保持执行完航点动作后的飞行器偏航角 飞至下一航点。',
    },
])

// 航点间云台俯仰角控制模式
const waypointElevationMode = ref([
    {
        value: 'manual',
        label: '手动控制',
        description:
            '手动控制：飞行器从一个航点飞向下一个航点的过程中，支持用户手飞行器偏航角模式：沿航线方向手动控制云台的俯仰角度；若无用户控制，则保持飞离航点时的云台俯仰角度。',
    },
    {
        value: 'usePointSetting',
        label: '依照每个航点设置',
        description: '依照每个航点设置：飞行器从一个航点飞向下一个航点的过程中，云台俯仰角均匀过渡至下一个航点的俯仰角。',
    },
])

// 完成动作
const waypointActionMode = ref([
    {
        value: 'goHome',
        label: '自动返航',
        description: '自动返航：飞行器航线任务完成后，立即飞向起飞点。若飞行器此时处于失联状态或飞向起飞点的过程中飞行器失联，则立即执行失联行为。',
    },
    {
        value: 'noAction',
        label: '退出航线模式',
        description: ' 退出航线模式：飞行器航线任务完成后，立即退出航线模式，并悬停在原点。若飞行器此时处于失联状态，则立即执行失联行为。',
    },
    {
        value: 'autoLand',
        label: '原地降落',
        description: ' 原地降落：飞行器航线任务完成后，立即开始降落。若飞行器此时处于失联状态或在降落过程中失联，则立即执行失联行为。',
    },
    {
        value: 'gotoFirstWaypoint',
        label: '返回航线起始点悬停',
        description:
            '返回航线起始点悬停：飞行器航线任务完成后，立即飞向起始点（S点）,若飞行器此时处于失联状态或飞向起始点（S点）的过程中飞行器失联，则立即执行失联行为。',
    },
])

const saveShipLanes = async () => {
    const res = await shipLanesStore.savePlacemark()
    if (res) {
        openShipLanesOptionsPop.value = ''
    }
}

const resetTakeOffPoint = () => {}
</script>

<style scoped lang="scss">
.flex {
    display: flex;
}

.justify-between {
    justify-content: space-between;
}

.align-center {
    align-items: center;
}

.justify-center {
    justify-content: center;
}

.flex-col {
    display: flex;
    flex-direction: column;
}

.flex-1 {
    flex: 1;
}

.gap-10 {
    gap: 10px;
}

.ship-lanes-options {
    position: absolute;
    left: 10px;
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

    .options-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 16px;

        .options-header-right {
            display: flex;
            align-items: center;
            gap: 10px;

            .back-icon {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
        }
    }

    .options-content {
        flex: 1;
        gap: 10px;
        overflow-y: auto;

        &::-webkit-scrollbar {
            display: none;
        }

        .content-item {
            background: #f6f6f6;
            padding: 12px;
            border-radius: 4px;
            gap: 10px;

            &-text {
                font-size: 12px;
                color: #000000;
            }

            .content-item-tabs {
                display: flex;
                padding: 2px;
                border-radius: 6px;
                background: #fff;
                box-shadow: 0px 0px 4px 0px #0000001a;

                .tabs-item {
                    flex: 1;
                    height: 37px;
                    text-align: center;
                    line-height: 37px;
                    font-size: 12px;
                    color: #333;
                    cursor: pointer;

                    &.active {
                        background: #00386d;
                        color: #fff;
                    }
                }
            }

            .content-item-bottom {
                gap: 10px;

                .bottom-icon {
                    flex: 1;
                    height: 130px;
                    background-color: rgb(53, 50, 50);
                }

                .bottom-content {
                    gap: 2px;

                    .bottom-content-item {
                        font-size: 12px;
                        background: #e4e4e4;
                        border-radius: 4px;
                        width: 50px;
                        height: 25px;
                        text-align: center;
                        line-height: 25px;
                        cursor: pointer;
                    }

                    .input-item {
                        background: transparent;
                    }

                    :deep(.el-input__wrapper) {
                        box-shadow: none;
                        border: none;
                        background: transparent;
                    }

                    :deep(.el-input__inner) {
                        text-align: center;
                    }
                }
            }

            .speed-item {
                width: 28px;
                height: 28px;
                border-radius: 4px;
                background: #e4e4e4;
                cursor: pointer;
            }

            .speed-input {
                :deep(.el-input__wrapper) {
                    box-shadow: none;
                    border: none;
                    background: transparent;
                    padding-right: 4px;
                }

                :deep(.el-input__inner) {
                    text-align: right;
                    color: #00386d;
                    font-weight: bold;
                    font-size: 18px;
                }
            }

            .title-wrapper {
                width: 100%;
                text-align: center;
            }

            :deep(.el-collapse-item__header) {
                background: transparent;
                border: none;
            }

            :deep(.el-collapse-item__wrap) {
                background: transparent;
                border: none;
                box-shadow: none;
            }

            :deep(.el-collapse-item__content) {
                padding: 0;
            }

            :deep(.el-select__wrapper) {
                box-shadow: none;
                border: none;
                background: #e4e4e4;
                padding: 0 10px;
            }
        }
    }
}
</style>
