<template>
    <el-dialog
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        title="新增用户"
        width="600px"
        class="add-device-dialog"
        @close="baTable.toggleForm"
        align-center
    >
        <el-form v-if="!baTable.form.loading" ref="formRef" :model="baTable.form.items" :rules="rules" label-position="top" class="device-form">
            <!-- 用户名和昵称 - 一行两列 -->
            <div class="flex device-row">
                <el-form-item :label="t('auth.admin.username')" prop="username" required class="flex-1">
                    <CustomInput v-model="baTable.form.items!.username" :placeholder="t('auth.admin.Administrator login')" width="100%" />
                </el-form-item>
                <el-form-item :label="t('auth.admin.nickname')" prop="nickname" required class="flex-1">
                    <CustomInput
                        v-model="baTable.form.items!.nickname"
                        :placeholder="t('Please input field', { field: t('auth.admin.nickname') })"
                        width="100%"
                    />
                </el-form-item>
            </div>

            <FormItem
                :label="t('auth.admin.group')"
                v-model="baTable.form.items!.group_arr"
                prop="group_arr"
                type="remoteSelect"
                :key="'group-' + baTable.form.items!.id"
                :input-attr="{
                    multiple: true,
                    params: { isTree: true, absoluteAuth: adminInfo.id == baTable.form.items!.id ? 0 : 1 },
                    field: 'name',
                    remoteUrl: '/admin/auth.Group/index',
                    placeholder: t('Click select'),
                }"
            />
            <FormItem :label="t('auth.admin.avatar')" type="image" v-model="baTable.form.items!.avatar" />
            <!-- 手机号和邮箱 -->
            <div class="flex device-row">
                <el-form-item :label="t('auth.admin.mobile')" prop="mobile" class="flex-1">
                    <CustomInput
                        v-model="baTable.form.items!.mobile"
                        :placeholder="t('Please input field', { field: t('auth.admin.mobile') })"
                        width="100%"
                    />
                </el-form-item>
                <el-form-item :label="t('auth.admin.email')" prop="email" class="flex-1">
                    <CustomInput
                        v-model="baTable.form.items!.email"
                        :placeholder="t('Please input field', { field: t('auth.admin.email') })"
                        width="100%"
                    />
                </el-form-item>
            </div>
            <!-- 密码 -->
            <el-form-item :label="t('auth.admin.Password')" required prop="password" class="flex-1">
                <CustomInput
                    v-model="baTable.form.items!.password"
                    :placeholder="t('Please input field', { field: t('auth.admin.Password') })"
                    width="100%"
                />
            </el-form-item>
            <!-- 个性签名 -->
            <el-form-item :label="t('auth.admin.Personal signature')" prop="motto" class="flex-1">
                <CustomInput
                    type="textarea"
                    v-model="baTable.form.items!.motto"
                    :placeholder="t('Please input field', { field: t('auth.admin.Personal signature') })"
                    width="100%"
                />
            </el-form-item>
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
import { ref, reactive, inject, watch } from 'vue'
import { type FormInstance } from 'element-plus'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import { buildValidatorData, regularPassword } from '/@/utils/validate'
import type { FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { useAdminInfo } from '/@/stores/adminInfo'

const { t } = useI18n()

const baTable = inject('baTable') as baTableClass
const adminInfo = useAdminInfo()

const formRef = ref<FormInstance>()

// 表单验证规则
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [buildValidatorData({ name: 'required', title: t('auth.admin.username') }), buildValidatorData({ name: 'account' })],
    nickname: [buildValidatorData({ name: 'required', title: t('auth.admin.nickname') })],
    group_arr: [buildValidatorData({ name: 'required', message: t('Please select field', { field: t('auth.admin.group') }) })],
    email: [buildValidatorData({ name: 'email', message: t('Please enter the correct field', { field: t('auth.admin.email') }) })],
    mobile: [buildValidatorData({ name: 'mobile', message: t('Please enter the correct field', { field: t('auth.admin.mobile') }) })],
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'Add') {
                    if (!val) {
                        return callback(new Error(t('Please input field', { field: t('auth.admin.Password') })))
                    }
                } else {
                    if (!val) {
                        return callback()
                    }
                }
                if (!regularPassword(val)) {
                    return callback(new Error(t('validate.Please enter the correct password')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})

watch(
    () => baTable.form.operate,
    (newVal) => {
        rules.password![0].required = newVal == 'Add'
    }
)
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

:deep(.el-select__wrapper) {
    height: 50px;
    background: white;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 0 12px;
    transition: all 0.3s ease;
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
