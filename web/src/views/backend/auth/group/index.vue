<template>
    <div class="group-box">
        <!-- <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon /> -->
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableSift :buttons="['add']" btnText="新增角色" />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" :pagination="false"></Table>

        <!-- 表单 -->
        <!-- <PopupForm ref="formRef" /> -->
        <PopupForm1 ref="formRef" />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
// import PopupForm from './popupForm.vue'
import { getAdminRules } from '/@/api/backend/auth/group'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import Table from '/@/components/table/index1.vue'
import TableSift from '/@/components/tableSift/index.vue'
import { useAdminInfo } from '/@/stores/adminInfo'
import baTableClass from '/@/utils/baTable'
import { getArrayKey } from '/@/utils/common'
import { uuid } from '/@/utils/random'
import PopupForm1 from './popupForm1.vue'

defineOptions({
    name: 'auth/group',
})

const { t } = useI18n()
const adminInfo = useAdminInfo()
const formRef = useTemplateRef('formRef')
const tableRef = useTemplateRef('tableRef')

const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

const baTable: baTableClass = new baTableClass(
    new baTableApi('/admin/auth.Group/'),
    {
        expandAll: true,
        dblClickNotEditColumn: [undefined],
        column: [
            { label: t('auth.group.Group name'), prop: 'name', align: 'left', width: '200' },
            { label: t('auth.group.jurisdiction'), prop: 'rules', align: 'center' },
            {
                label: t('State'),
                prop: 'status',
                align: 'center',
                render: 'tag',
                custom: { 0: 'danger', 1: 'success' },
                replaceValue: { 0: t('Disable'), 1: t('Enable') },
            },
            { label: t('Update time'), prop: 'update_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Create time'), prop: 'create_time', align: 'center', width: '160', render: 'datetime' },
            { label: t('Operate'), align: 'center', width: '130', render: 'buttons', buttons: optButtons },
        ],
    },
    {
        defaultItems: {
            status: 1,
        },
    }
)

// 利用提交前钩子重写提交操作
baTable.before.onSubmit = ({ formEl, operate, items }) => {
    let submitCallback = () => {
        baTable.form.submitLoading = true
        baTable.api
            .postData(operate, {
                ...items,
                rules: formRef.value?.getCheckeds(),
            })
            .then((res) => {
                baTable.onTableHeaderAction('refresh', {})
                baTable.form.submitLoading = false
                baTable.form.operateIds?.shift()
                if (baTable.form.operateIds!.length > 0) {
                    baTable.toggleForm('Edit', baTable.form.operateIds)
                } else {
                    baTable.toggleForm()
                }
                baTable.runAfter('onSubmit', { res })
            })
            .catch(() => {
                baTable.form.submitLoading = false
            })
    }

    if (formEl) {
        baTable.form.ref = formEl
        formEl.validate((valid) => {
            if (valid) {
                submitCallback()
            }
        })
    } else {
        submitCallback()
    }
    return false
}

// 利用双击单元格前钩子重写双击操作
baTable.before.onTableDblclick = ({ row }) => {
    return baTable.table.extend!.adminGroup.indexOf(row.id) === -1
}

// 获取到数据后钩子
baTable.after.getData = ({ res }) => {
    baTable.table.extend!.adminGroup = res.data.group
    let buttonsKey = getArrayKey(baTable.table.column, 'render', 'buttons')
    baTable.table.column[buttonsKey].buttons!.forEach((value: OptButton) => {
        value.display = (row) => {
            return res.data.group.indexOf(row.id) === -1
        }
    })
}

// 切换表单后钩子
baTable.after.toggleForm = ({ operate }) => {
    if (operate == 'Add') {
        menuRuleTreeUpdate()
    }
}

// 编辑请求完成后钩子
baTable.after.getEditData = () => {
    menuRuleTreeUpdate()
}

const menuRuleTreeUpdate = () => {
    getAdminRules().then((res) => {
        baTable.form.extend!.menuRules = res.data.list

        if (baTable.form.items!.rules && baTable.form.items!.rules.length) {
            if (baTable.form.items!.rules.includes('*')) {
                let arr: number[] = []
                for (const key in baTable.form.extend!.menuRules) {
                    arr.push(baTable.form.extend!.menuRules[key].id)
                }
                baTable.form.extend!.defaultCheckedKeys = arr
            } else {
                baTable.form.extend!.defaultCheckedKeys = baTable.form.items!.rules
            }
        } else {
            baTable.form.extend!.defaultCheckedKeys = []
        }
        baTable.form.extend!.treeKey = uuid()
    })
}

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()
})
</script>

<style scoped lang="scss">
.group-box {
    background: #fff;
    height: 100%;
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
</style>
