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
                        :label="t('equipment.alarm.equipment_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.equipment_id"
                        prop="equipment_id"
                        :input-attr="{ pk: 'equipment.id', field: 'nickname', remoteUrl: '/admin/Equipment/index' }"
                        :placeholder="t('Please select field', { field: t('equipment.alarm.equipment_id') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.level')"
                        type="select"
                        v-model="baTable.form.items!.level"
                        prop="level"
                        :input-attr="{
                            content: { '0': t('equipment.alarm.level 0'), '1': t('equipment.alarm.level 1'), '2': t('equipment.alarm.level 2') },
                        }"
                        :placeholder="t('Please select field', { field: t('equipment.alarm.level') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.module')"
                        type="select"
                        v-model="baTable.form.items!.module"
                        prop="module"
                        :input-attr="{
                            content: {
                                '0': t('equipment.alarm.module 0'),
                                '1': t('equipment.alarm.module 1'),
                                '2': t('equipment.alarm.module 2'),
                                '3': t('equipment.alarm.module 3'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('equipment.alarm.module') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.in_the_sky')"
                        type="select"
                        v-model="baTable.form.items!.in_the_sky"
                        prop="in_the_sky"
                        :input-attr="{ content: { '0': t('equipment.alarm.in_the_sky 0'), '1': t('equipment.alarm.in_the_sky 1') } }"
                        :placeholder="t('Please select field', { field: t('equipment.alarm.in_the_sky') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.code')"
                        type="string"
                        v-model="baTable.form.items!.code"
                        prop="code"
                        :placeholder="t('Please input field', { field: t('equipment.alarm.code') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.device_type')"
                        type="string"
                        v-model="baTable.form.items!.device_type"
                        prop="device_type"
                        :placeholder="t('Please input field', { field: t('equipment.alarm.device_type') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.imminent')"
                        type="select"
                        v-model="baTable.form.items!.imminent"
                        prop="imminent"
                        :input-attr="{ content: { '0': t('equipment.alarm.imminent 0'), '1': t('equipment.alarm.imminent 1') } }"
                        :placeholder="t('Please select field', { field: t('equipment.alarm.imminent') })"
                    />
                    <FormItem
                        :label="t('equipment.alarm.content')"
                        type="string"
                        v-model="baTable.form.items!.content"
                        prop="content"
                        :placeholder="t('Please input field', { field: t('equipment.alarm.content') })"
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
    create_time: [buildValidatorData({ name: 'date', title: t('equipment.alarm.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('equipment.alarm.update_time') })],
})
</script>

<style scoped lang="scss"></style>
