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
                        :label="t('project.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('project.name') })"
                    />
                    <FormItem
                        :label="t('project.introduction')"
                        type="textarea"
                        v-model="baTable.form.items!.introduction"
                        prop="introduction"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('project.introduction') })"
                    />
                    <FormItem
                        :label="t('project.longitude')"
                        type="string"
                        v-model="baTable.form.items!.longitude"
                        prop="longitude"
                        :placeholder="t('Please input field', { field: t('project.longitude') })"
                    />
                    <FormItem
                        :label="t('project.latitude')"
                        type="string"
                        v-model="baTable.form.items!.latitude"
                        prop="latitude"
                        :placeholder="t('Please input field', { field: t('project.latitude') })"
                    />
                    <FormItem
                        :label="t('project.is_stop')"
                        type="switch"
                        v-model="baTable.form.items!.is_stop"
                        prop="is_stop"
                        :input-attr="{ content: { '0': t('project.is_stop 0'), '1': t('project.is_stop 1') } }"
                    />
                    <FormItem
                        :label="t('project.wind_speed')"
                        type="number"
                        v-model="baTable.form.items!.wind_speed"
                        prop="wind_speed"
                        :input-attr="{ step: 1 }"
                        :placeholder="t('Please input field', { field: t('project.wind_speed') })"
                    />
                    <FormItem
                        :label="t('project.rainfall')"
                        type="select"
                        v-model="baTable.form.items!.rainfall"
                        prop="rainfall"
                        :input-attr="{
                            content: {
                                '0': t('project.rainfall 0'),
                                '1': t('project.rainfall 1'),
                                '2': t('project.rainfall 2'),
                                '3': t('project.rainfall 3'),
                            },
                        }"
                        :placeholder="t('Please select field', { field: t('project.rainfall') })"
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
    longitude: [buildValidatorData({ name: 'required', title: t('project.longitude') })],
    latitude: [buildValidatorData({ name: 'required', title: t('project.latitude') })],
    wind_speed: [buildValidatorData({ name: 'number', title: t('project.wind_speed') })],
    create_time: [buildValidatorData({ name: 'date', title: t('project.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('project.update_time') })],
})
</script>

<style scoped lang="scss"></style>
