<template>
    <el-dialog
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        title="新增项目"
        width="600px"
        class="add-device-dialog"
        @close="baTable.toggleForm"
    >
        <el-form v-if="!baTable.form.loading" ref="formRef" :model="baTable.form.items" :rules="rules" label-position="top" class="device-form">
            <!-- 项目名称和项目介绍 - 一行两列 -->
            <el-form-item label="项目名称" prop="name" required class="flex-1">
                <CustomInput v-model="baTable.form.items!.name" placeholder="请输入项目名称" width="100%" />
            </el-form-item>

            <el-form-item label="项目介绍" prop="introduction" required class="flex-1">
                <CustomInput type="textarea" v-model="baTable.form.items!.introduction" placeholder="请输入项目介绍" width="100%" />
            </el-form-item>

            <!-- 经纬度 - 一行两列 -->
            <div class="flex device-row">
                <el-form-item label="经度" prop="longitude" required class="flex-1">
                    <CustomInput v-model="baTable.form.items!.longitude" placeholder="请输入经度" width="100%" />
                </el-form-item>
                <el-form-item label="纬度" prop="latitude" required class="flex-1">
                    <CustomInput v-model="baTable.form.items!.latitude" placeholder="请输入纬度" width="100%" />
                </el-form-item>
                <el-button type="primary" @click="selectAddress">选择地址</el-button>
            </div>
            <!-- 云端阻飞开关 -->
            <!-- <el-form-item :label="t('project.is_stop')" prop="is_stop" required class="flex-1">
                <CustomRadioGroup v-model="baTable.form.items!.is_stop" :options="isStopOptions" />
            </el-form-item> -->

            <!-- 云端阻飞风速和雨量 - 一行两列 -->
            <!-- <div class="flex device-row">
                <el-form-item label="云端阻飞风速" prop="wind_speed" class="flex-1">
                    <CustomInput type="number" v-model="baTable.form.items!.wind_speed" placeholder="请输入云端阻飞风速" width="100%" />
                </el-form-item>
                <el-form-item label="云端阻飞雨量" prop="rainfall" class="flex-1">
                    <CustomSelect v-model="baTable.form.items!.rainfall" placeholder="请选择云端阻飞雨量" :options="rainfallOptions" width="100%" />
                </el-form-item>
            </div> -->
        </el-form>

        <template #footer>
            <div class="dialog-footer">
                <CustomButton variant="secondary" @click="baTable.toggleForm()">取消</CustomButton>
                <CustomButton variant="primary" @click="baTable.onSubmit(formRef)">提交保存</CustomButton>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, inject } from 'vue'
import { ElMessage, type FormInstance, type FormRules } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomSelect from '/@/components/form/CustomSelect.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import CustomRadioGroup from '/@/components/form/CustomRadioGroup.vue'
import { buildValidatorData } from '/@/utils/validate'
import type { FormItemRule } from 'element-plus'

const { t } = useI18n()

interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'submit', data: any): void
}

const baTable = inject('baTable') as baTableClass

const emit = defineEmits<Emits>()

const formRef = ref<FormInstance>()

// 云端阻飞雨量选项
const rainfallOptions = [
    { value: '0', label: t('project.rainfall 0') },
    { value: '1', label: t('project.rainfall 1') },
    { value: '2', label: t('project.rainfall 2') },
    { value: '3', label: t('project.rainfall 3') },
]

// 云端阻飞风速选项
const isStopOptions = [
    { value: 0, label: t('project.is_stop 0') },
    { value: 1, label: t('project.is_stop 1') },
]

// 表单验证规则
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('project.name') })],
    introduction: [buildValidatorData({ name: 'required', title: t('project.introduction') })],
    longitude: [buildValidatorData({ name: 'required', title: t('project.longitude') })],
    latitude: [buildValidatorData({ name: 'required', title: t('project.latitude') })],
    // is_stop: [buildValidatorData({ name: 'required', title: t('project.is_stop') })],
    // wind_speed: [buildValidatorData({ name: 'number', title: t('project.wind_speed') })],
    // rainfall: [buildValidatorData({ name: 'required', title: t('project.rainfall') })],
})

const selectAddress = () => {
    console.log('选择地址')
}
</script>

<style scoped>
.add-device-dialog {
    border-radius: 24px;
}

/* Element Plus 表单标签样式 */
:deep(.el-form-item__label) {
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.9);
    margin-bottom: 8px;
    padding: 0;
    line-height: 1.2;
}

:deep(.el-form-item.is-required .el-form-item__label::before) {
    content: '*';
    color: #d54941;
    margin-right: 4px;
}

:deep(.el-form-item) {
    margin-bottom: 24px;
}

.device-form {
    width: 100%;
}

.device-row {
    display: flex;
    gap: 16px;
    width: 100%;
    align-items: center;
}

.flex-1 {
    flex: 1;
}

/* 底部按钮样式 */
.dialog-footer {
    display: flex;
    justify-content: center;
    gap: 16px;
}

/* 响应式设计 */
@media screen and (max-width: 768px) {
    .device-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>
