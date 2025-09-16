<template>
    <div class="media-api-management">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">媒体接口配置</h1>
        </div>
        <el-form ref="formRef" :model="mediaConfig" :rules="formRules" label-position="top" class="media-form">
            <!-- 推流类型 -->
            <el-form-item prop="streamType" label="推流类型" required>
                <div class="form-row">
                    <CustomSelect
                        v-model="mediaConfig.streamType"
                        placeholder="请选择推流类型"
                        :options="streamTypeOptions"
                        width="480px"
                        @change="handleStreamTypeChange"
                    />
                </div>
            </el-form-item>

            <!-- Access Key ID -->
            <el-form-item prop="accessKeyId" label="Access Key ID" required>
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.accessKeyId"
                        placeholder="请输入Access Key ID"
                        width="480px"
                        :maxlength="100"
                        :show-word-limit="true"
                    />
                </div>
            </el-form-item>

            <!-- Access Key Secret -->
            <el-form-item prop="accessKeySecret" label="Access Key Secret" required>
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.accessKeySecret"
                        type="password"
                        placeholder="请输入Access Key Secret"
                        width="480px"
                        :maxlength="100"
                        :show-word-limit="true"
                        show-password
                    />
                </div>
            </el-form-item>

            <!-- 推流地址 -->
            <el-form-item prop="streamUrl" label="推流地址" required>
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.streamUrl"
                        placeholder="请输入推流地址"
                        width="480px"
                        :maxlength="200"
                        :show-word-limit="true"
                    />
                </div>
            </el-form-item>
        </el-form>

        <!-- 提交按钮 -->
        <div class="submit-section">
            <CustomButton variant="primary" :loading="submitting" @click="handleSubmit"> 提交保存 </CustomButton>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomSelect from '/@/components/form/CustomSelect.vue'
import CustomButton from '/@/components/form/CustomButton.vue'

const formRef = ref<FormInstance>()
const submitting = ref(false)

// 推流类型选项
const streamTypeOptions = [
    { value: 'rtmp', label: 'RTMP推流' },
    { value: 'hls', label: 'HLS推流' },
    { value: 'webrtc', label: 'WebRTC推流' },
    { value: 'srt', label: 'SRT推流' },
]

// 媒体配置数据
const mediaConfig = ref({
    streamType: '',
    accessKeyId: '',
    accessKeySecret: '',
    streamUrl: '',
})

// 表单验证规则
const formRules = reactive<FormRules>({
    streamType: [{ required: true, message: '请选择推流类型', trigger: 'change' }],
    accessKeyId: [
        { required: true, message: '请输入Access Key ID', trigger: 'blur' },
        { min: 10, max: 100, message: 'Access Key ID长度在 10 到 100 个字符', trigger: 'blur' },
    ],
    accessKeySecret: [
        { required: true, message: '请输入Access Key Secret', trigger: 'blur' },
        { min: 10, max: 100, message: 'Access Key Secret长度在 10 到 100 个字符', trigger: 'blur' },
    ],
    streamUrl: [
        { required: true, message: '请输入推流地址', trigger: 'blur' },
        {
            pattern: /^(rtmp|rtmps|http|https):\/\/.+/,
            message: '请输入有效的推流地址',
            trigger: 'blur',
        },
    ],
})

// 处理推流类型变化
const handleStreamTypeChange = (value: string | number | boolean) => {
    console.log('推流类型变化:', value)
    // 根据推流类型设置默认的推流地址前缀
    if (value === 'rtmp') {
        mediaConfig.value.streamUrl = 'rtmp://'
    } else if (value === 'hls') {
        mediaConfig.value.streamUrl = 'https://'
    } else if (value === 'webrtc') {
        mediaConfig.value.streamUrl = 'https://'
    } else if (value === 'srt') {
        mediaConfig.value.streamUrl = 'srt://'
    }
}

// 处理提交
const handleSubmit = async () => {
    if (!formRef.value) return

    try {
        submitting.value = true

        // 表单验证
        await formRef.value.validate()

        // 确认提交
        await ElMessageBox.confirm('确定要保存媒体接口配置吗？', '确认提交', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'info',
        })

        // 模拟提交延迟
        await new Promise((resolve) => setTimeout(resolve, 1000))

        // 模拟提交
        ElMessage.success('媒体接口配置已保存')
        console.log('保存媒体接口配置:', mediaConfig.value)
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
.media-api-management {
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
    justify-content: center;
    gap: 40px;
}

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

/* 表单样式 */
.media-form {
    width: 480px;
    margin-bottom: 40px;
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

.form-row {
    display: flex;
    align-items: center;
}

/* 提交按钮区域 */
.submit-section {
    display: flex;
    justify-content: center;
}

/* 响应式设计 */
@media screen and (max-width: 768px) {
    .media-form {
        width: 100%;
        padding: 0 20px;
    }

    .form-row {
        width: 100%;
    }
}
</style>
