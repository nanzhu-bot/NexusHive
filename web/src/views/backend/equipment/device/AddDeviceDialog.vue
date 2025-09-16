<template>
    <el-dialog
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        title="新增设备"
        width="800px"
        @close="baTable.toggleForm"
        class="add-device-dialog"
    >
        <el-form
            ref="formRef"
            v-loading="loading"
            v-if="!baTable.form.loading"
            :model="baTable.form.items"
            :rules="rules"
            label-position="top"
            class="device-form"
        >
            <div class="flex device-row">
                <el-form-item label="设备厂商" prop="manufacturer" required class="flex-1">
                    <CustomSelect v-model="baTable.form.items!.manufacturer" placeholder="请选择设备" :options="deviceOptions" width="100%" />
                </el-form-item>
                <el-form-item label="绑定设备" prop="project_id" required class="flex-1">
                    <CustomSelect v-model="baTable.form.items!.project_id" placeholder="所属项目" :options="projectOptions" width="100%" />
                </el-form-item>
            </div>

            <!-- <el-form-item :label="t('equipment.mode_code')" prop="mode_code" required>
                <CustomSelect v-model="baTable.form.items!.mode_code" placeholder="所属项目" :options="projectOptions" width="100%" />
            </el-form-item> -->

            <!-- 设备名称和设备型号 - 一行两列 -->
            <div class="flex device-row">
                <el-form-item label="设备名称" prop="nickname" required class="flex-1">
                    <CustomInput v-model="baTable.form.items!.nickname" placeholder="请输入设备名称" width="100%" />
                </el-form-item>
                <el-form-item label="设备型号" prop="model" required class="flex-1">
                    <CustomSelect v-model="baTable.form.items!.model" placeholder="请选择设备型号" width="100%" :options="modelOptions" />
                </el-form-item>
            </div>

            <!-- 设备SN码和固件版本 - 一行两列 -->
            <div class="flex device-row">
                <el-form-item label="设备SN码" prop="sn" required class="flex-1">
                    <CustomInput v-model="baTable.form.items!.sn" placeholder="请输入设备SN码" width="100%" append>
                        <template #append>
                            <el-button type="primary" @click="handleSN" size="small">效验SN码</el-button>
                        </template>
                    </CustomInput>
                </el-form-item>
                <el-form-item class="flex-1"> </el-form-item>
            </div>
        </el-form>

        <template #footer>
            <div class="dialog-footer">
                <CustomButton variant="secondary" @click="baTable.toggleForm()">取消</CustomButton>
                <CustomButton variant="primary" @click="handleSubmit">提交保存</CustomButton>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive, inject, onMounted, computed } from 'vue'
import { ElMessage, type FormInstance, type FormRules } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomSelect from '/@/components/form/CustomSelect.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import { useMqttStore } from '/@/stores/mqtt'

const { t } = useI18n()

interface Emits {
    (e: 'update:modelValue', value: boolean): void
    (e: 'submit', data: any): void
}

const mqttStore = useMqttStore()

const deviceOsds = computed(() => mqttStore.deviceOsds)

onMounted(() => {
    getProjectList()
})

// 获取项目列表
const getProjectList = async () => {
    const res = await projectApi.index()
    projectOptions.value = res.data.list.map((item: any) => ({
        value: item.id,
        label: item.name,
    }))
    console.log(projectOptions.value)
}

const projectApi = new baTableApi('/admin/Project/')

const equipmentApi = new baTableApi('/admin/Equipment/')

const baTable = inject('baTable') as baTableClass

const emit = defineEmits<Emits>()
// 表单ref
const formRef = ref<FormInstance>()
// 是否校验SN码
const isValidateSN = ref(false)

const loading = ref(false)

// 设备选项
const deviceOptions = [
    { value: '0', label: t('equipment.manufacturer 0') },
    { value: '1', label: t('equipment.manufacturer 1') },
]

// 项目选项
const projectOptions = ref([])

// 设备型号选项
const modelOptions = ref([
    { value: '3-2-0', label: '大疆机场2' },
    { value: '3-3-0', label: '大疆机场3' },
])

// 表单数据
const form = reactive({
    deviceType: '',
    deviceName: '',
    deviceModel: '',
    deviceSN: '',
    firmwareVersion: '',
})

// 表单验证规则
const rules: FormRules = {
    manufacturer: [{ required: true, message: '请选择设备厂商', trigger: 'change' }],
    project_id: [{ required: true, message: '请选择所属项目', trigger: 'change' }],
    nickname: [
        { required: true, message: '请输入设备名称', trigger: 'blur' },
        { min: 2, max: 50, message: '设备名称长度在 2 到 50 个字符', trigger: 'blur' },
    ],
    model: [
        { required: true, message: '请输入设备型号', trigger: 'blur' },
        { min: 2, max: 50, message: '设备型号长度在 2 到 50 个字符', trigger: 'blur' },
    ],
    sn: [
        { required: true, message: '请输入设备SN码', trigger: 'blur' },
        { min: 5, max: 50, message: 'SN码长度在 5 到 50 个字符', trigger: 'blur' },
    ],
}

// 处理提交
const handleSubmit = async () => {
    if (!isValidateSN.value) {
        ElMessage.error('请先效验SN码')
        return
    }
    console.log(deviceOsds.value[baTable.form.items!.sn])
    try {
        await formRef.value?.validate()

        // 添加
        if (baTable.form.operate! === 'Add') {
            equipmentApi
                .add({
                    ...baTable.form.items,
                    osd: deviceOsds.value[baTable.form.items!.sn],
                })
                .then((res) => {
                    ElMessage.success('设备添加成功')
                    baTable.onTableHeaderAction('refresh', {})
                    baTable.toggleForm()
                })
            return
        }
        // 编辑
        if (baTable.form.operate! === 'Edit') {
            equipmentApi
                .editPost({
                    ...baTable.form.items,
                    osd: deviceOsds.value[baTable.form.items!.sn],
                })
                .then((res) => {
                    ElMessage.success('设备编辑成功')
                    baTable.onTableHeaderAction('refresh', {})
                    baTable.toggleForm()
                })
        }
    } catch (error) {
        ElMessage.error('请检查表单信息')
    }
}

// 效验SN码
const handleSN = () => {
    // 先判断sn码是否已存在
    const sn = baTable.form.items!.sn
    if (deviceOsds.value[sn]) {
        isValidateSN.value = true
        ElMessage.success('SN码效验成功')
        return
    } else {
        deviceOsds.value[sn] = {}
    }
    loading.value = true
    mqttStore.subscribe(`thing/product/${sn}/osd`, 0)
    setTimeout(() => {
        loading.value = false
        if (deviceOsds.value[sn].latitude) {
            isValidateSN.value = true
            ElMessage.success('SN码效验成功')
        } else {
            ElMessage.error('SN码错误')
            mqttStore.unsubscribe(`thing/product/${sn}/osd`)
            delete deviceOsds.value[sn]
        }
        console.log(deviceOsds.value)
    }, 2000)
}
</script>

<style scoped>
:deep(.el-input-group__append) {
    box-shadow: none;
    border-radius: 0 12px 12px 0;
}

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
