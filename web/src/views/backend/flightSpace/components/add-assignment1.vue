<!-- 新建任务 -->
<template>
    <div class="add-assignment">
        <div class="add-assignment-header">
            <div class="add-assignment-header-title">新建任务</div>
            <el-icon size="16" style="cursor: pointer" @click="close"><Close /></el-icon>
        </div>
        <el-form ref="formRef" :model="form" :rules="rules" label-position="top" class="task-form" style="flex: 1">
            <div class="add-assignment-content">
                <!-- 任务名称 -->
                <div class="content-item">
                    <FormItem label="任务名称" required type="string" v-model="form.name" prop="name" placeholder="请输入任务名称" />
                </div>
                <!-- 任务类型 -->
                <div class="content-item">
                    <!-- 航线精度类型 -->
                    <el-form-item label="任务类型" prop="task_sub_type" class="flex-1">
                        <div class="content-item-content1">
                            <div class="content-item-tab" :class="{ active: form.task_sub_type === 0 }" @click="form.task_sub_type = 0">立即执行</div>
                            <div class="content-item-tab" :class="{ active: form.task_sub_type === 1 }" @click="form.task_sub_type = 1">定时执行</div>
                        </div>
                    </el-form-item>
                </div>
                <!-- 开始执行时间 -->
                <div class="content-item">
                    <FormItem
                        class="flex-1"
                        :label="t('flighttask.execute_time')"
                        type="datetime"
                        v-model="form.execute_time"
                        prop="execute_time"
                        required
                        :placeholder="t('Please select field', { field: t('flighttask.execute_time') })"
                    />
                </div>
                <!-- 执行航线 -->
                <div class="content-item">
                    <FormItem
                        class="flex-1"
                        :label="t('flighttask.airline_id')"
                        type="remoteSelect"
                        v-model="form.airline_id"
                        prop="airline_id"
                        :input-attr="{ pk: 'airline.id', field: 'name', remoteUrl: '/admin/Airline/index' }"
                        :placeholder="t('Please select field', { field: t('flighttask.airline_id') })"
                        @change="changeAirline"
                        required
                    />
                </div>

                <!-- 执行设备 -->
                <div class="content-item">
                    <FormItem
                        class="flex-1"
                        :label="t('flighttask.equipment_id')"
                        type="remoteSelect"
                        v-model="form.equipment_id"
                        prop="equipment_id"
                        :input-attr="{ pk: 'equipment.id', field: 'nickname', remoteUrl: '/admin/Equipment/index' }"
                        :placeholder="t('Please select field', { field: '执行设备' })"
                        required
                        @change="changeEquipment"
                    />
                </div>
                <div class="content-item">
                    <!-- 航线精度类型 -->
                    <el-form-item :label="t('flighttask.wayline_precision_type')" prop="wayline_precision_type" class="flex-1">
                        <div class="content-item-content1">
                            <div
                                class="content-item-tab"
                                :class="{ active: form.wayline_precision_type === '0' }"
                                @click="form.wayline_precision_type = '0'"
                            >
                                {{ t('flighttask.wayline_precision_type 0') }}
                            </div>
                            <div
                                class="content-item-tab"
                                :class="{ active: form.wayline_precision_type === '1' }"
                                @click="form.wayline_precision_type = '1'"
                            >
                                {{ t('flighttask.wayline_precision_type 1') }}
                            </div>
                        </div>
                    </el-form-item>
                </div>
                <!-- 返回高度 -->
                <div class="content-item">
                    <el-form-item :label="t('flighttask.rth_altitude')" prop="rth_altitude" class="flex-1">
                        <el-input
                            v-model="form.rth_altitude"
                            :placeholder="t('Please input field', { field: t('flighttask.rth_altitude') })"
                            width="100%"
                            type="number"
                            :min="0"
                            :max="1000"
                        />
                    </el-form-item>
                </div>
                <!-- 遥控器失控动作 -->
                <div class="content-item">
                    <el-form-item :label="t('flighttask.out_of_control_action')" prop="out_of_control_action" class="flex-1">
                        <div class="content-item-content1">
                            <div
                                class="content-item-tab"
                                :class="{ active: form.out_of_control_action === '0' }"
                                @click="form.out_of_control_action = '0'"
                            >
                                {{ t('flighttask.out_of_control_action 0') }}
                            </div>
                            <div
                                class="content-item-tab"
                                :class="{ active: form.out_of_control_action === '1' }"
                                @click="form.out_of_control_action = '1'"
                            >
                                {{ t('flighttask.out_of_control_action 1') }}
                            </div>
                            <div
                                class="content-item-tab"
                                :class="{ active: form.out_of_control_action === '2' }"
                                @click="form.out_of_control_action = '2'"
                            >
                                {{ t('flighttask.out_of_control_action 2') }}
                            </div>
                        </div>
                    </el-form-item>
                </div>
                <!-- 返航高度模式 -->
                <div class="content-item">
                    <el-form-item :label="t('flighttask.rth_mode')" prop="rth_mode" class="flex-1">
                        <div class="content-item-content1">
                            <div class="content-item-tab" :class="{ active: form.rth_mode === '0' }" @click="form.rth_mode = '0'">
                                {{ t('flighttask.rth_mode 0') }}
                            </div>
                            <div class="content-item-tab" :class="{ active: form.rth_mode === '1' }" @click="form.rth_mode = '1'">
                                {{ t('flighttask.rth_mode 1') }}
                            </div>
                        </div>
                    </el-form-item>
                </div>
                <!-- 按钮 -->
                <div class="add-assignment-footer">
                    <el-button variant="secondary" type="default" @click="close">取消</el-button>
                    <el-button variant="primary" type="primary" @click="submit">提交保存</el-button>
                </div>
            </div>
        </el-form>
    </div>
</template>
<script setup lang="ts">
import { ref, inject } from 'vue'
import { Close } from '@element-plus/icons-vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type { FormInstance, FormRules } from 'element-plus'
import { useShipLanes } from '/@/stores/shipLanes'
import { storeToRefs } from 'pinia'
import { baTableApi } from '/@/api/common'
import { useMapStore } from '/@/stores/map'
import { useMqttStore } from '/@/stores/mqtt'
import { disposition } from '/@/config/disposition'
import { ElMessage } from 'element-plus'

const { t } = useI18n()
// api
const api = new baTableApi('/admin/Flighttask/')

// 打开新建任务弹窗
const isShowAssignmentPop = inject<any>('isShowAssignmentPop')

// 是否显示左侧
const isShowLeft = inject<any>('isShowLeft')

const shipLanesStore = useShipLanes()
const { activeShipLanes, shipStartPoint } = storeToRefs(shipLanesStore)
const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')

const mapStore = useMapStore()
const { isShowDroneLive, initialHeight } = storeToRefs(mapStore)
const { graphic_d } = storeToRefs(mapStore)
const mqttStore = useMqttStore()
const { gateway_sn, deviceOsds, droneData } = storeToRefs(mqttStore)

const aerodromeInfo = inject<any>('aerodromeInfo')

// 表单引用
const formRef = ref<FormInstance>()

const form = ref({
    task_type: 0,
    task_sub_type: 0,
    airline_id: '',
    name: '',
    execute_time: '',
    wayline_precision_type: '1',
    rth_altitude: 0,
    equipment_id: '',
    out_of_control_action: '0',
    rth_mode: '0',
    exit_wayline_when_rc_lost: '0',
})

const kmzJson = ref({})

// 表单验证规则
const rules = ref<FormRules>({
    name: [
        { required: true, message: '请输入任务名称', trigger: 'blur' },
        { min: 1, max: 50, message: '任务名称长度在 1 到 50 个字符', trigger: 'blur' },
    ],
    airline_id: [{ required: true, message: '请选择执行航线', trigger: 'change' }],
    equipment_id: [{ required: true, message: '请选择执行设备', trigger: 'change' }],
    execute_time: [{ required: true, message: '请选择开始执行时间', trigger: 'change' }],
    rth_altitude: [
        { required: true, message: '请输入返回高度', trigger: 'blur' },
        {
            validator: (rule, value, callback) => {
                if (value === '' || value === null || value === undefined) {
                    callback(new Error('请输入返回高度'))
                } else if (isNaN(Number(value))) {
                    callback(new Error('返回高度必须是数字'))
                } else if (Number(value) < 0) {
                    callback(new Error('返回高度不能小于0'))
                } else if (Number(value) > 1000) {
                    callback(new Error('返回高度不能大于1000米'))
                } else {
                    callback()
                }
            },
            trigger: 'blur',
        },
    ],
})

const changeAirline = (val: any) => {
    if (val) {
        activeShipLanes.value = 1
        kmzJson.value = JSON.parse(val.kmz_json)
        shipLanesStore.showShipForm(kmzJson.value)
        openShipLanesOptionsPop.value = 'look'
        console.log(val)
    } else {
        activeShipLanes.value = ''
        openShipLanesOptionsPop.value = ''
    }
}

const changeEquipment = (val: any) => {
    if (val) {
        aerodromeInfo.value = val
        gateway_sn.value = val.sn
        const graphic = graphic_d.value.filter((item1: any) => item1.attr.sn === val.sn)
        initialHeight.value = parseFloat(graphic[0]._point._alt)
    }
}

const close = () => {
    isShowAssignmentPop.value = false
    isShowLeft.value = true
    openShipLanesOptionsPop.value = ''
}

const submit = async () => {
    if (!formRef.value) return
    try {
        await formRef.value.validate()
        // 这里可以添加实际的提交逻辑
        // 例如调用API保存数据
        await api.add(form.value)
        ElMessage.success('新建任务成功')
        isShowAssignmentPop.value = false
        isShowLeft.value = true
        mapStore.createRoute()
        // isShowDroneLive.value = true
        disposition.djiDock.gateway_sn = gateway_sn.value
        disposition.device.device_sn = deviceOsds.value[gateway_sn.value].sub_device.device_sn
    } catch (error) {
        console.error('创建任务失败:', error)
    }
}
</script>
<style scoped lang="scss">
.add-assignment {
    position: absolute;
    left: 10px;
    top: 10px;
    bottom: 10px;
    width: 280px;
    display: flex;
    flex-direction: column;
    z-index: 99;
    box-shadow: 0px 0px 4px 0px #0000001a;
    padding: 16px;
    background-color: #fff;
    border-radius: 12px;
    overflow: auto;
    &::-webkit-scrollbar {
        display: none;
    }

    .add-assignment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
    }

    .add-assignment-content {
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        padding: 10px 0 70px;
        position: relative;
        height: 100%;

        .content-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .content-item-title {
            font-size: 14px;
            font-weight: bold;
        }

        .content-item-content {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .content-item-content1 {
            width: 100%;
            display: flex;
            padding: 2px;
            border: 1px solid #dcdcdc;
            background: #fff;
            border-radius: 6px;

            .content-item-tab {
                flex: 1;
                height: 30px;
                text-align: center;
                line-height: 30px;
                font-size: 14px;
                color: #333;
                cursor: pointer;
                border-radius: 4px;

                &.active {
                    background: #00386d;
                    color: #fff;
                }
            }
        }
    }

    .add-assignment-footer {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 16px;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 60px;
        padding: 10px;
        background-color: #fff;
        border-top: 1px solid #e5e5e5;
    }
}

:deep(.el-input__wrapper) {
    height: 32px;
}
</style>
