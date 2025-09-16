<template>
    <el-dialog
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        title="新建任务"
        width="750px"
        @close="baTable.toggleForm"
        class="add-assignment-dialog"
        draggable
        align-center
        :loading="baTable.form.loading"
    >
        <div class="header">
            <div class="header-item">
                <div class="header-item-title">
                    任务类型
                    <span class="required">*</span>
                </div>
                <div class="header-item-content">
                    <div class="content-item" :class="{ active: form.task_type === 0 }" @click="form.task_type = 0">单次任务</div>
                    <div class="content-item" :class="{ active: form.task_type === 1 }" @click="form.task_type = 1">循环任务</div>
                </div>
            </div>
            <div class="header-item">
                <div class="header-item-title">
                    任务类型
                    <span class="required">*</span>
                </div>
                <div class="header-item-content">
                    <!-- <div class="content-item" :class="{ active: form.task_sub_type === 0 }" @click="form.task_sub_type = 0">双机巡检</div> -->
                    <div class="content-item" :class="{ active: form.task_sub_type === 0 }" @click="form.task_sub_type = 0">立即执行</div>
                    <div class="content-item" :class="{ active: form.task_sub_type === 1 }" @click="form.task_sub_type = 1">定时执行</div>
                    <!-- <div class="content-item" :class="{ active: form.task_sub_type === 2 }" @click="form.task_sub_type = 2">条件执行</div> -->
                </div>
            </div>
        </div>
        <div class="content">
            <div class="content-header">
                <el-tabs v-model="activeTab" class="demo-tabs">
                    <el-tab-pane :label="item.label" :name="item.value" v-for="(item, index) in activeOptionList" :key="index">
                        <el-form
                            v-if="!baTable.form.loading"
                            ref="formRef"
                            :model="baTable.form.items"
                            :rules="rules"
                            label-position="top"
                            class="device-form"
                        >
                            <!-- 航线和飞行器 -->
                            <div class="flex device-row">
                                <FormItem
                                    class="flex-1"
                                    :label="t('flighttask.airline_id')"
                                    type="remoteSelect"
                                    v-model="baTable.form.items!.airline_id"
                                    prop="airline_id"
                                    :input-attr="{ pk: 'airline.id', field: 'name', remoteUrl: '/admin/Airline/index' }"
                                    :placeholder="t('Please select field', { field: t('flighttask.airline_id') })"
                                    @change="changeAirline"
                                />
                                <FormItem
                                    class="flex-1"
                                    :label="t('flighttask.equipment_id')"
                                    type="remoteSelect"
                                    v-model="baTable.form.items!.equipment_id"
                                    prop="equipment_id"
                                    :input-attr="{ pk: 'equipment.id', field: 'nickname', remoteUrl: '/admin/Equipment/index' }"
                                    :placeholder="t('Please select field', { field: t('flighttask.equipment_id') })"
                                />
                            </div>
                            <!-- 开始时间和返回高度 -->
                            <div class="flex device-row">
                                <FormItem
                                    class="flex-1"
                                    :label="t('flighttask.execute_time')"
                                    type="datetime"
                                    v-model="baTable.form.items!.execute_time"
                                    prop="execute_time"
                                    required
                                    :placeholder="t('Please select field', { field: t('flighttask.execute_time') })"
                                />
                                <el-form-item :label="t('flighttask.rth_altitude')" prop="rth_altitude" class="flex-1">
                                    <CustomInput
                                        v-model="baTable.form.items!.rth_altitude"
                                        :placeholder="t('Please input field', { field: t('flighttask.rth_altitude') })"
                                        width="100%"
                                        type="number"
                                    />
                                </el-form-item>
                            </div>

                            <!-- 航线文件上传 -->
                            <!-- <div class="flex device-row">
                                <FormItem
                                    class="flex-1"
                                    :label="t('flighttask.file_url')"
                                    type="file"
                                    v-model="baTable.form.items!.file_url"
                                    prop="file_url"
                                />
                            </div> -->

                            <div class="flex device-row">
                                <el-form-item :label="t('flighttask.rth_mode')" prop="rth_mode" class="flex-1">
                                    <div class="header-item-content">
                                        <div
                                            class="content-item"
                                            :class="{ active: baTable.form.items!.rth_mode === '0' }"
                                            @click="baTable.form.items!.rth_mode = '0'"
                                        >
                                            {{ t('flighttask.rth_mode 0') }}
                                        </div>
                                        <div
                                            class="content-item"
                                            :class="{ active: baTable.form.items!.rth_mode === '1' }"
                                            @click="baTable.form.items!.rth_mode = '1'"
                                        >
                                            {{ t('flighttask.rth_mode 1') }}
                                        </div>
                                    </div>
                                </el-form-item>
                                <el-form-item :label="t('flighttask.out_of_control_action')" prop="out_of_control_action" class="flex-1">
                                    <div class="header-item-content">
                                        <div
                                            class="content-item"
                                            :class="{ active: baTable.form.items!.out_of_control_action === '0' }"
                                            @click="baTable.form.items!.out_of_control_action = '0'"
                                        >
                                            {{ t('flighttask.out_of_control_action 0') }}
                                        </div>
                                        <div
                                            class="content-item"
                                            :class="{ active: baTable.form.items!.out_of_control_action === '1' }"
                                            @click="baTable.form.items!.out_of_control_action = '1'"
                                        >
                                            {{ t('flighttask.out_of_control_action 1') }}
                                        </div>
                                        <div
                                            class="content-item"
                                            :class="{ active: baTable.form.items!.out_of_control_action === '2' }"
                                            @click="baTable.form.items!.out_of_control_action = '2'"
                                        >
                                            {{ t('flighttask.out_of_control_action 2') }}
                                        </div>
                                    </div>
                                </el-form-item>
                            </div>
                            <!-- 航线失控动作 -->
                            <el-form-item :label="t('flighttask.exit_wayline_when_rc_lost')" prop="exit_wayline_when_rc_lost" class="flex-1">
                                <div class="header-item-content">
                                    <div
                                        class="content-item"
                                        :class="{ active: baTable.form.items!.exit_wayline_when_rc_lost === '0' }"
                                        @click="baTable.form.items!.exit_wayline_when_rc_lost = '0'"
                                    >
                                        {{ t('flighttask.exit_wayline_when_rc_lost 0') }}
                                    </div>
                                    <div
                                        class="content-item"
                                        :class="{ active: baTable.form.items!.exit_wayline_when_rc_lost === '1' }"
                                        @click="baTable.form.items!.exit_wayline_when_rc_lost = '1'"
                                    >
                                        {{ t('flighttask.exit_wayline_when_rc_lost 1') }}
                                    </div>
                                    <div
                                        class="content-item"
                                        :class="{ active: baTable.form.items!.exit_wayline_when_rc_lost === '2' }"
                                        @click="baTable.form.items!.exit_wayline_when_rc_lost = '2'"
                                    >
                                        {{ t('flighttask.exit_wayline_when_rc_lost 2') }}
                                    </div>
                                </div>
                            </el-form-item>
                            <!-- 航线精度类型 -->
                            <el-form-item :label="t('flighttask.wayline_precision_type')" prop="wayline_precision_type" class="flex-1">
                                <div class="header-item-content">
                                    <div
                                        class="content-item"
                                        :class="{ active: baTable.form.items!.wayline_precision_type === '0' }"
                                        @click="baTable.form.items!.wayline_precision_type = '0'"
                                    >
                                        {{ t('flighttask.wayline_precision_type 0') }}
                                    </div>
                                    <div
                                        class="content-item"
                                        :class="{ active: baTable.form.items!.wayline_precision_type === '1' }"
                                        @click="baTable.form.items!.wayline_precision_type = '1'"
                                    >
                                        {{ t('flighttask.wayline_precision_type 1') }}
                                    </div>
                                </div>
                            </el-form-item>
                        </el-form>
                    </el-tab-pane>
                </el-tabs>
            </div>
        </div>
        <template #footer>
            <div class="dialog-footer">
                <CustomButton variant="secondary" @click="baTable.toggleForm()">取消</CustomButton>
                <CustomButton variant="primary" @click="submit">提交保存</CustomButton>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, inject, reactive } from 'vue'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'
import { useI18n } from 'vue-i18n'
import type { FormItemRule } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import FormItem from '/@/components/formItem/index.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type { FormInstance } from 'element-plus'
import { useMapStore } from '/@/stores/map'
import { useShipLanes } from '/@/stores/shipLanes'
import { storeToRefs } from 'pinia'
import { DJIoperations } from '/@/utils/mqttSdk'
import { disposition } from '/@/config/disposition'
import { useMqttStore } from '/@/stores/mqtt'

const { t } = useI18n()

const baTable = inject('baTable') as baTableClass
const formRef = ref<FormInstance[]>()

const shipLanesStore = useShipLanes()
const { activeShipLanes, shipStartPoint } = storeToRefs(shipLanesStore)

const mapStore = useMapStore()
const { isShowLive } = storeToRefs(mapStore)

const mqttStore = useMqttStore()
const { gateway_sn, deviceOsds, droneData } = storeToRefs(mqttStore)

const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')

// 打开任务弹窗
const openAssignmentPop = inject<any>('openAssignmentPop')

const form = ref({
    task_type: 0,
    task_sub_type: 0,
})

// 选中的tab
const activeTab = ref(0)

const activeOptionList = ref([
    {
        label: '飞行器1',
        value: 0,
    },
])

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    execute_time: [buildValidatorData({ name: 'date', title: t('flighttask.execute_time') })],
    create_time: [buildValidatorData({ name: 'date', title: t('flighttask.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('flighttask.update_time') })],
})

const kmzJson = ref({})

const changeAirline = (value: any) => {
    if (value) {
        kmzJson.value = JSON.parse(value.kmz_json)
        console.log(kmzJson.value)
    }
}

const submit = () => {
    try {
        baTable.onSubmit(formRef.value![activeTab.value])
        openAssignmentPop.value = false
        activeShipLanes.value = 1
        shipLanesStore.showShipForm(kmzJson.value)
        openShipLanesOptionsPop.value = 'look'
        mapStore.createRoute()
        isShowLive.value = true
        disposition.djiDock.gateway_sn = gateway_sn.value
        disposition.device.device_sn = deviceOsds.value[gateway_sn.value].sub_device.device_sn
    } catch (error) {
        console.log(error)
    }
}
</script>

<style scoped lang="scss">
.add-assignment-dialog {
    border-radius: 24px;

    .header {
        display: flex;
        align-items: center;
        gap: 28px;
        margin-bottom: 40px;

        .header-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;

            .header-item-title {
                font-size: 14px;
                color: #333;
                display: flex;
                gap: 4px;
                align-items: center;

                .required {
                    color: #f56c6c;
                    font-size: 12px;
                }
            }
        }
    }

    .content {
        display: flex;
        flex-direction: column;
        gap: 24px;
        background: #f6f6f6;
        border-radius: 10px;
        padding: 0 24px;
        height: 50vh;
        overflow-y: auto;

        .content-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
    }
}

.device-row {
    display: flex;
    gap: 16px;
    width: 100%;
}

.header-item-content {
    width: 100%;
    display: flex;
    padding: 2px;
    border: 1px solid #dcdcdc;
    background: #fff;
    border-radius: 6px;

    .content-item {
        flex: 1;
        height: 44px;
        text-align: center;
        line-height: 44px;
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

.flex-1 {
    flex: 1;
}

:deep(.el-input__wrapper) {
    height: 50px;
    background: white;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 0 12px;
    transition: all 0.3s ease;
}

:deep(.el-select__wrapper) {
    height: 50px;
    background: white;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 0 12px;
    transition: all 0.3s ease;
}

/* 底部按钮样式 */
.dialog-footer {
    display: flex;
    justify-content: center;
    gap: 16px;
}
</style>
