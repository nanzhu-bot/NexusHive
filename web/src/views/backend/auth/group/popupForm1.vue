<template>
    <el-dialog
        :model-value="['Add', 'Edit'].includes(baTable.form.operate!)"
        title="新增角色"
        width="600px"
        class="add-device-dialog"
        @close="baTable.toggleForm"
        align-center
        :destroy-on-close="true"
        :close-on-click-modal="false"
    >
        <el-form v-if="!baTable.form.loading" ref="formRef" :model="baTable.form.items" :rules="rules" label-position="top" class="device-form">
            <!-- 父级角色 -->
            <FormItem
                :label="t('auth.group.Parent group')"
                v-model="baTable.form.items!.pid"
                prop="pid"
                type="remoteSelect"
                :key="'group-' + baTable.form.items!.id"
                :input-attr="{
                    params: { isTree: true },
                    field: 'name',
                    remoteUrl: baTable.api.actionUrl.get('index'),
                    placeholder: t('Click select'),
                    emptyValues: ['', null, undefined, 0],
                    valueOnClear: 0,
                }"
            />
            <!-- 角色名称 -->
            <el-form-item label="角色名称" prop="name" class="flex-1">
                <CustomInput v-model="baTable.form.items!.name" :placeholder="t('Please input field', { field: '角色名称' })" width="100%" />
            </el-form-item>
            <el-form-item :label="t('State')" prop="status" class="flex-1">
                <CustomRadioGroup v-model="baTable.form.items!.status" :options="statusOptions" />
            </el-form-item>
            <!-- 权限 -->
            <div class="auth-box">
                <el-form-item prop="auth" :label="t('auth.group.jurisdiction')">
                    <el-tree
                        ref="treeRef"
                        :key="baTable.form.extend!.treeKey"
                        :default-checked-keys="baTable.form.extend!.defaultCheckedKeys"
                        :default-expand-all="true"
                        show-checkbox
                        node-key="id"
                        :props="{ children: 'children', label: 'title', class: treeNodeClass }"
                        :data="baTable.form.extend!.menuRules"
                        class="w100"
                    />
                </el-form-item>
            </div>
        </el-form>

        <template #footer>
            <div class="dialog-footer">
                <CustomButton variant="secondary" @click="baTable.toggleForm('')">取消</CustomButton>
                <CustomButton :loading="baTable.form.submitLoading" variant="primary" @click="baTable.onSubmit(formRef)">提交保存</CustomButton>
            </div>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import { reactive, inject, useTemplateRef } from 'vue'
import CustomInput from '/@/components/form/CustomInput.vue'
import CustomButton from '/@/components/form/CustomButton.vue'
import type baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import { buildValidatorData } from '/@/utils/validate'
import type { FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import CustomRadioGroup from '/@/components/form/CustomRadioGroup.vue'
import type Node from 'element-plus/es/components/tree/src/model/node'

const { t } = useI18n()

const formRef = useTemplateRef('formRef')
const treeRef = useTemplateRef('treeRef')
const baTable = inject('baTable') as baTableClass

const statusOptions = [
    { value: 1, label: t('Enable') },
    { value: 0, label: t('Disable') },
]

// 表单验证规则
const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [buildValidatorData({ name: 'required', title: t('auth.group.Group name') })],
    auth: [
        {
            required: true,
            validator: (rule: any, val: string, callback: Function) => {
                let ids = getCheckeds()
                if (ids.length <= 0) {
                    return callback(new Error(t('Please select field', { field: t('auth.group.jurisdiction') })))
                }
                return callback()
            },
        },
    ],
    pid: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (!val) {
                    return callback()
                }
                if (parseInt(val) == parseInt(baTable.form.items!.id)) {
                    return callback(new Error(t('auth.group.The parent group cannot be the group itself')))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})

const getCheckeds = () => {
    return treeRef.value!.getCheckedKeys().concat(treeRef.value!.getHalfCheckedKeys())
}

const treeNodeClass = (data: anyObj, node: Node) => {
    if (node.isLeaf) return ''
    let addClass = true
    for (const key in node.childNodes) {
        if (!node.childNodes[key].isLeaf) {
            addClass = false
        }
    }
    return addClass ? 'penultimate-node' : ''
}

defineExpose({ getCheckeds })
</script>

<style scoped>
.add-device-dialog {
    border-radius: 24px;
    height: 70vh;
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

.auth-box {
    height: 30vh;
    overflow: auto;
}

:deep(.el-checkbox__input.is-checked .el-checkbox__inner) {
    background: #00386d;
    border-color: #00386d;
}

:deep(.el-checkbox__inner) {
    &:hover {
        border-color: #00386d;
    }
}

:deep(.penultimate-node) {
    .el-tree-node__children {
        padding-left: 60px;
        white-space: pre-wrap;
        line-height: 12px;
        .el-tree-node {
            display: inline-block;
        }
        .el-tree-node__content {
            padding-left: 5px !important;
            padding-right: 5px;
            .el-tree-node__expand-icon {
                display: none;
            }
        }
    }
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
