<template>
    <div class="basic-info-management">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">基本信息管理</h1>
        </div>
        <!-- 基本信息表单 -->
        <div class="form-container">
            <el-form ref="formRef" :model="basicInfo" :rules="formRules" class="basic-form" label-position="top">
                <!-- 选择需要配置的平台 -->
                <el-form-item prop="platform" label="选择需要配置的平台" required>
                    <CustomRadioGroup v-model="basicInfo.platform" :options="platformOptions" @change="handlePlatformChange" />
                </el-form-item>

                <!-- 系统LOGO -->
                <el-form-item prop="logoUrl" label="系统LOGO" required>
                    <div class="logo-upload">
                        <div class="logo-preview">
                            <img :src="basicInfo.logoUrl || '/src/assets/head.png'" alt="系统LOGO" class="logo-image" />
                        </div>
                        <el-upload
                            ref="uploadRef"
                            :show-file-list="false"
                            :before-upload="beforeUpload"
                            :on-success="handleUploadSuccess"
                            :on-error="handleUploadError"
                            action="#"
                            :http-request="customUpload"
                        >
                            <el-button class="upload-btn"> 上传LOGO </el-button>
                        </el-upload>
                    </div>
                </el-form-item>

                <!-- 系统名称 -->
                <el-form-item prop="systemName" label="系统名称" required>
                    <CustomInput
                        v-model="basicInfo.systemName"
                        placeholder="请输入系统名称"
                        :show-word-limit="true"
                        :error-message="fieldErrors.systemName || ''"
                        @blur="() => formRef?.validateField('systemName')"
                    />
                </el-form-item>

                <!-- 备案号 -->
                <el-form-item prop="recordNumber" label="备案号" required>
                    <CustomInput
                        v-model="basicInfo.recordNumber"
                        placeholder="请输入备案号"
                        :show-word-limit="true"
                        :error-message="fieldErrors.recordNumber || ''"
                        @blur="() => formRef?.validateField('recordNumber')"
                    />
                </el-form-item>

                <!-- 系统基本信息 -->
                <el-form-item prop="basicInfo" label="系统基本信息" required>
                    <CustomInput
                        v-model="basicInfo.basicInfo"
                        placeholder="请输入系统基本信息"
                        :show-word-limit="true"
                        :error-message="fieldErrors.basicInfo || ''"
                        @blur="() => formRef?.validateField('basicInfo')"
                    />
                </el-form-item>
            </el-form>
        </div>

        <!-- 提交按钮 -->
        <div class="submit-section">
            <CustomButton variant="primary" :loading="submitting" width="120px" @click="handleSubmit"> 提交保存 </CustomButton>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules, UploadProps, UploadRequestOptions } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import CustomRadioGroup from '/@/components/form/CustomRadioGroup.vue'

const formRef = ref<FormInstance>()
const uploadRef = ref()
const submitting = ref(false)
const fieldErrors = ref<Record<string, string>>({})

// 平台选项配置
const platformOptions = [
    {
        value: 'backend',
        label: '后台',
    },
    {
        value: 'miniprogram',
        label: '小程序',
    },
]

// 基本信息数据
const basicInfo = ref({
    platform: 'backend', // 'backend' | 'miniprogram'
    logoUrl: '',
    systemName: '',
    recordNumber: '',
    basicInfo: '',
})

// 表单验证规则
const formRules = reactive<FormRules>({
    platform: [{ required: true, message: '请选择配置平台', trigger: 'change' }],
    logoUrl: [{ required: true, message: '请上传系统LOGO', trigger: 'change' }],
    systemName: [
        { required: true, message: '请输入系统名称', trigger: 'blur' },
        { min: 2, max: 50, message: '系统名称长度在 2 到 50 个字符', trigger: 'blur' },
    ],
    recordNumber: [
        { required: true, message: '请输入备案号', trigger: 'blur' },
        {
            pattern: /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领]ICP备\d{8}号(-\d+)?$/,
            message: '请输入正确的备案号格式',
            trigger: 'blur',
        },
    ],
    basicInfo: [
        { required: true, message: '请输入系统基本信息', trigger: 'blur' },
        { min: 10, max: 200, message: '系统基本信息长度在 10 到 200 个字符', trigger: 'blur' },
    ],
})

// 上传前验证
const beforeUpload: UploadProps['beforeUpload'] = (file) => {
    const isImage = file.type.startsWith('image/')
    const isLt2M = file.size / 1024 / 1024 < 2

    if (!isImage) {
        ElMessage.error('只能上传图片文件!')
        return false
    }
    if (!isLt2M) {
        ElMessage.error('上传图片大小不能超过 2MB!')
        return false
    }
    return true
}

// 自定义上传方法
const customUpload = (options: UploadRequestOptions) => {
    const file = options.file
    const reader = new FileReader()

    reader.onload = (e) => {
        basicInfo.value.logoUrl = e.target?.result as string
        ElMessage.success('LOGO上传成功')

        // 触发表单验证
        formRef.value?.validateField('logoUrl')
    }

    reader.onerror = () => {
        ElMessage.error('LOGO上传失败')
    }

    reader.readAsDataURL(file)
}

// 上传成功回调
const handleUploadSuccess = () => {
    ElMessage.success('LOGO上传成功')
}

// 上传失败回调
const handleUploadError = () => {
    ElMessage.error('LOGO上传失败，请重试')
}

// 处理平台变化
const handlePlatformChange = (value: string | number | boolean) => {
    console.log('平台选择变化:', value)
    // 触发表单验证
    formRef.value?.validateField('platform')
}

// 获取字段错误信息
const getFieldError = (field: string): string => {
    return fieldErrors.value[field] || ''
}

// 处理提交
const handleSubmit = async () => {
    if (!formRef.value) return

    try {
        submitting.value = true
        fieldErrors.value = {}

        // 表单验证
        await formRef.value.validate()

        // 确认提交
        await ElMessageBox.confirm('确定要保存基本信息设置吗？', '确认提交', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'info',
        })

        // 模拟提交延迟
        await new Promise((resolve) => setTimeout(resolve, 1000))

        // 模拟提交
        ElMessage.success('基本信息已保存')
        console.log('保存基本信息:', basicInfo.value)
    } catch (error) {
        if (error !== 'cancel') {
            ElMessage.error('表单验证失败，请检查输入内容')
        }
    } finally {
        submitting.value = false
    }
}
</script>

<style scoped>
.page-header {
    padding: 10px 15px;
    display: flex;
    align-items: center;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #000000;
    margin: 0;
}
.basic-info-management {
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    gap: 40px;
}

/* 表单容器 */
.form-container {
    width: 480px;
    margin-bottom: 40px;
}

.basic-form {
    width: 100%;
}

/* 表单项 */
.form-item {
    margin-bottom: 32px;
}

.form-item:last-child {
    margin-bottom: 0;
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
    margin-bottom: 32px;
}

:deep(.el-form-item:last-child) {
    margin-bottom: 0;
}

/* 自定义单选框组件样式已移至 CustomRadioGroup.vue */

/* LOGO上传 */
.logo-upload {
    display: flex;
    align-items: center;
    gap: 16px;
}

.logo-preview {
    width: 80px;
    height: 80px;
}

.logo-image {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
}

:deep(.el-upload) {
    display: inline-block;
}

.upload-btn {
    width: 96px;
    height: 38px;
    background: #e4e9ed;
    border: none;
    border-radius: 12px;
    font-size: 13px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.8);
}

.upload-btn:hover {
    background: #d5dde3;
}

/* 表单输入框 */
.form-input {
    width: 100%;
}

:deep(.form-input .el-input__wrapper) {
    height: 50px;
    background: white;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 0 12px;
}

/* 提交按钮区域 */
.submit-section {
    display: flex;
    justify-content: center;
}

.submit-btn {
    width: 120px;
    height: 48px;
    background: #00386d;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: white;
}

.submit-btn:hover {
    background: #004080;
}
</style>
