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
                        :label="t('equipment.manufacturer')"
                        type="select"
                        v-model="baTable.form.items!.manufacturer"
                        prop="manufacturer"
                        :input-attr="{ content: { '0': t('equipment.manufacturer 0'), '1': t('equipment.manufacturer 1') } }"
                        :placeholder="t('Please select field', { field: t('equipment.manufacturer') })"
                    />
                    <FormItem
                        :label="t('equipment.model')"
                        type="string"
                        v-model="baTable.form.items!.model"
                        prop="model"
                        :placeholder="t('Please input field', { field: t('equipment.model') })"
                    />
                    <FormItem
                        :label="t('equipment.nickname')"
                        type="string"
                        v-model="baTable.form.items!.nickname"
                        prop="nickname"
                        :placeholder="t('Please input field', { field: t('equipment.nickname') })"
                    />
                    <FormItem
                        :label="t('equipment.sn')"
                        type="string"
                        v-model="baTable.form.items!.sn"
                        prop="sn"
                        :placeholder="t('Please input field', { field: t('equipment.sn') })"
                    />
                    <FormItem
                        :label="t('equipment.project_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.project_id"
                        prop="project_id"
                        :input-attr="{ pk: 'project.id', field: 'name', remoteUrl: '/admin/Project/index' }"
                        :placeholder="t('Please select field', { field: t('equipment.project_id') })"
                    />
                    <FormItem
                        :label="t('equipment.firmware_version')"
                        type="string"
                        v-model="baTable.form.items!.firmware_version"
                        prop="firmware_version"
                        :placeholder="t('Please input field', { field: t('equipment.firmware_version') })"
                    />
                    <FormItem
                        :label="t('equipment.mode_code')"
                        type="select"
                        v-model="baTable.form.items!.mode_code"
                        prop="mode_code"
                        :input-attr="{
                            content: {
                                '0': t('equipment.mode_code 0'),
                                '1': t('equipment.mode_code 1'),
                                '2': t('equipment.mode_code 2'),
                                '3': t('equipment.mode_code 3'),
                                '4': t('equipment.mode_code 4'),
                                '5': t('equipment.mode_code 5'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('equipment.mode_code') })"
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
    manufacturer: [buildValidatorData({ name: 'required', title: t('equipment.manufacturer') })],
    create_time: [buildValidatorData({ name: 'date', title: t('equipment.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('equipment.update_time') })],
})
</script>

<style scoped lang="scss"></style>
