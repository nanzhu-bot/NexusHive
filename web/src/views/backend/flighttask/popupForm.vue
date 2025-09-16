<template>
    <!-- 对话框表单 -->
    <!-- 建议使用 Prettier 格式化代码 -->
    <!-- el-form 内可以混用 el-form-item、FormItem、ba-input 等输入组件 -->
    <el-dialog
        class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        @close="baTable.toggleForm"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="config.layout.shrink ? '' : 'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @submit.prevent=""
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    :label-position="config.layout.shrink ? 'top' : 'right'"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <FormItem
                        :label="t('flighttask.bid')"
                        type="string"
                        v-model="baTable.form.items!.bid"
                        prop="bid"
                        :placeholder="t('Please input field', { field: t('flighttask.bid') })"
                    />
                    <FormItem
                        :label="t('flighttask.tid')"
                        type="string"
                        v-model="baTable.form.items!.tid"
                        prop="tid"
                        :placeholder="t('Please input field', { field: t('flighttask.tid') })"
                    />
                    <FormItem
                        :label="t('flighttask.airline_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.airline_id"
                        prop="airline_id"
                        :input-attr="{ pk: 'airline.id', field: 'name', remoteUrl: '/admin/Airline/index' }"
                        :placeholder="t('Please select field', { field: t('flighttask.airline_id') })"
                    />
                    <FormItem
                        :label="t('flighttask.equipment_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.equipment_id"
                        prop="equipment_id"
                        :input-attr="{ pk: 'equipment.id', field: 'nickname', remoteUrl: '/admin/Equipment/index' }"
                        :placeholder="t('Please select field', { field: t('flighttask.equipment_id') })"
                    />
                    <FormItem
                        :label="t('flighttask.execute_time')"
                        type="datetime"
                        v-model="baTable.form.items!.execute_time"
                        prop="execute_time"
                        :placeholder="t('Please select field', { field: t('flighttask.execute_time') })"
                    />
                    <FormItem
                        :label="t('flighttask.task_type')"
                        type="select"
                        v-model="baTable.form.items!.task_type"
                        prop="task_type"
                        :input-attr="{
                            content: { '0': t('flighttask.task_type 0'), '1': t('flighttask.task_type 1'), '2': t('flighttask.task_type 2') },
                        }"
                        :placeholder="t('Please select field', { field: t('flighttask.task_type') })"
                    />
                    <FormItem :label="t('flighttask.file_url')" type="file" v-model="baTable.form.items!.file_url" prop="file_url" />
                    <FormItem
                        :label="t('flighttask.file_fingerprint')"
                        type="string"
                        v-model="baTable.form.items!.file_fingerprint"
                        prop="file_fingerprint"
                        :placeholder="t('Please input field', { field: t('flighttask.file_fingerprint') })"
                    />
                    <FormItem
                        :label="t('flighttask.rth_altitude')"
                        type="number"
                        v-model="baTable.form.items!.rth_altitude"
                        prop="rth_altitude"
                        :input-attr="{ step: 1 }"
                        :placeholder="t('Please input field', { field: t('flighttask.rth_altitude') })"
                    />
                    <FormItem
                        :label="t('flighttask.rth_mode')"
                        type="select"
                        v-model="baTable.form.items!.rth_mode"
                        prop="rth_mode"
                        :input-attr="{ content: { '0': t('flighttask.rth_mode 0'), '1': t('flighttask.rth_mode 1') } }"
                        :placeholder="t('Please select field', { field: t('flighttask.rth_mode') })"
                    />
                    <FormItem
                        :label="t('flighttask.out_of_control_action')"
                        type="select"
                        v-model="baTable.form.items!.out_of_control_action"
                        prop="out_of_control_action"
                        :input-attr="{
                            content: {
                                '0': t('flighttask.out_of_control_action 0'),
                                '1': t('flighttask.out_of_control_action 1'),
                                '2': t('flighttask.out_of_control_action 2'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('flighttask.out_of_control_action') })"
                    />
                    <FormItem
                        :label="t('flighttask.exit_wayline_when_rc_lost')"
                        type="select"
                        v-model="baTable.form.items!.exit_wayline_when_rc_lost"
                        prop="exit_wayline_when_rc_lost"
                        :input-attr="{
                            content: {
                                '0': t('flighttask.exit_wayline_when_rc_lost 0'),
                                '1': t('flighttask.exit_wayline_when_rc_lost 1'),
                                '2': t('flighttask.exit_wayline_when_rc_lost 2'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('flighttask.exit_wayline_when_rc_lost') })"
                    />
                    <FormItem
                        :label="t('flighttask.wayline_precision_type')"
                        type="select"
                        v-model="baTable.form.items!.wayline_precision_type"
                        prop="wayline_precision_type"
                        :input-attr="{ content: { '0': t('flighttask.wayline_precision_type 0'), '1': t('flighttask.wayline_precision_type 1') } }"
                        :placeholder="t('Please select field', { field: t('flighttask.wayline_precision_type') })"
                    />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm()">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import type { FormItemRule } from 'element-plus'
import { inject, reactive, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import FormItem from '/@/components/formItem/index.vue'
import { useConfig } from '/@/stores/config'
import type baTableClass from '/@/utils/baTable'
import { buildValidatorData } from '/@/utils/validate'

const config = useConfig()
const formRef = useTemplateRef('formRef')
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    execute_time: [buildValidatorData({ name: 'date', title: t('flighttask.execute_time') })],
    rth_altitude: [buildValidatorData({ name: 'number', title: t('flighttask.rth_altitude') })],
    create_time: [buildValidatorData({ name: 'date', title: t('flighttask.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('flighttask.update_time') })],
})
</script>

<style scoped lang="scss"></style>
