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
                        :label="t('airline.name')"
                        type="string"
                        v-model="baTable.form.items!.name"
                        prop="name"
                        :placeholder="t('Please input field', { field: t('airline.name') })"
                    />
                    <FormItem
                        :label="t('airline.project_id')"
                        type="remoteSelect"
                        v-model="baTable.form.items!.project_id"
                        prop="project_id"
                        :input-attr="{ pk: 'project.id', field: 'name', remoteUrl: '/admin/Project/index' }"
                        :placeholder="t('Please select field', { field: t('airline.project_id') })"
                    />
                    <FormItem
                        :label="t('airline.type')"
                        type="select"
                        v-model="baTable.form.items!.type"
                        prop="type"
                        :input-attr="{ content: { '0': t('airline.type 0'), '1': t('airline.type 1') } }"
                        :placeholder="t('Please select field', { field: t('airline.type') })"
                    />
                    <FormItem :label="t('airline.template')" type="file" v-model="baTable.form.items!.template" prop="template" />
                    <FormItem :label="t('airline.wayline')" type="file" v-model="baTable.form.items!.wayline" prop="wayline" />
                    <FormItem :label="t('airline.kmz')" type="file" v-model="baTable.form.items!.kmz" prop="kmz" />
                    <FormItem
                        :label="t('airline.kmz_md5')"
                        type="string"
                        v-model="baTable.form.items!.kmz_md5"
                        prop="kmz_md5"
                        :placeholder="t('Please input field', { field: t('airline.kmz_md5') })"
                    />
                    <FormItem
                        :label="t('airline.kmz_json')"
                        type="textarea"
                        v-model="baTable.form.items!.kmz_json"
                        prop="kmz_json"
                        :input-attr="{ rows: 3 }"
                        @keyup.enter.stop=""
                        @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                        :placeholder="t('Please input field', { field: t('airline.kmz_json') })"
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
    create_time: [buildValidatorData({ name: 'date', title: t('airline.create_time') })],
    update_time: [buildValidatorData({ name: 'date', title: t('airline.update_time') })],
})
</script>

<style scoped lang="scss"></style>
