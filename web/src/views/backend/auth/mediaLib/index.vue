<template>
    <div class="media-lib-management">
        <el-form ref="formRef" :model="mediaConfig" label-position="top" class="media-form">
            <!-- 存储方式 -->
            <el-form-item prop="storageType" label="存储方式">
                <div class="form-row">
                    <CustomSelect v-model="mediaConfig.upload_mode" placeholder="选择存储方式" :options="storageTypeOptions" width="480px" />
                </div>
            </el-form-item>

            <!-- Bucket名称 -->
            <el-form-item prop="bucketName" label="Bucket名称">
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.upload_bucket"
                        placeholder="请输入Bucket名称"
                        width="480px"
                        :maxlength="100"
                        :show-word-limit="true"
                    />
                </div>
            </el-form-item>

            <!-- Accesskey ID -->
            <el-form-item prop="accessKeyId" label="Accesskey ID">
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.upload_access_id"
                        placeholder="请输入Accesskey ID"
                        width="480px"
                        :maxlength="100"
                        :show-word-limit="true"
                    />
                </div>
            </el-form-item>

            <!-- Accesskey Secret -->
            <el-form-item prop="accessKeySecret" label="Accesskey Secret">
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.upload_secret_key"
                        type="password"
                        placeholder="请输入Accesskey Secret"
                        width="480px"
                        :maxlength="100"
                        :show-word-limit="true"
                        show-password
                    />
                </div>
            </el-form-item>

            <!-- 存储区域 -->
            <el-form-item prop="region" label="存储区域">
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.upload_url"
                        placeholder="请输入存储区域"
                        width="480px"
                        :maxlength="50"
                        :show-word-limit="true"
                    />
                </div>
            </el-form-item>

            <!-- CDN地址 -->
            <el-form-item prop="cdnUrl" label="CDN地址">
                <div class="form-row">
                    <CustomInput
                        v-model="mediaConfig.upload_cdn_url"
                        placeholder="请输入CDN地址"
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
import { ref, reactive, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomSelect from '/@/components/form/CustomSelect.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import { baTableApi } from '/@/api/common'
import createAxios from '/@/utils/axios'

onMounted(() => {
    getMediaConfig()
})

const getMediaConfig = async () => {
    const res = await api.index()
    let arr = res.data.list.upload.list
    let obj: any = {}
    arr.forEach((item: any) => {
        obj[item.name] = item.value
    })
    // 将obj赋值给mediaConfig
    setMediaConfig(obj)
}

const api = new baTableApi('/admin/routine.Config/')

const formRef = ref<FormInstance>()
const submitting = ref(false)

// 存储方式选项
const storageTypeOptions = [{ value: 'alioss', label: '阿里云对象存储OSS' }]

// 媒体库配置数据
const mediaConfig = ref({
    upload_mode: '',
    upload_bucket: '',
    upload_access_id: '',
    upload_secret_key: '',
    upload_url: '',
    upload_cdn_url: '',
})

const setMediaConfig = (obj: any) => {
    for (let key in mediaConfig.value) {
        mediaConfig.value[key as keyof typeof mediaConfig.value] = obj[key]
    }
}

// 处理提交
const handleSubmit = async () => {
    if (!formRef.value) return

    try {
        submitting.value = true

        // 确认提交
        await ElMessageBox.confirm('确定要保存媒体库配置吗？', '确认提交', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'info',
        })

        // 模拟提交延迟
        await saveMediaConfig()

        // 模拟提交
        ElMessage.success('媒体库配置已保存')
        console.log('保存媒体库配置:', mediaConfig.value)
    } catch (error) {
        ElMessage.error('保存失败')
    } finally {
        submitting.value = false
    }
}

// 保存媒体库配置
const saveMediaConfig = async () => {
    return createAxios(
        {
            url: '/admin/routine.Config/edit',
            method: 'post',
            data: mediaConfig.value,
        },
        {
            showSuccessMessage: true,
        }
    )
}
</script>

<style scoped>
.media-lib-management {
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
