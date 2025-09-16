<template>
    <div class="project-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">项目管理</h1>
        </div>

        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableSift :buttons="['search', 'daterange', 'add']" quick-search-placeholder="项目搜索" />

        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef"></Table>

        <!-- 表单 -->
        <!-- <PopupForm /> -->
        <PopupForm1 />
        <PopupMap />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef } from 'vue'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import PopupForm1 from './popupForm1.vue'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import Table from '/@/components/table/index1.vue'
import baTableClass from '/@/utils/baTable'
import TableSift from '/@/components/tableSift/index.vue'

defineOptions({
    name: 'project',
})

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')
const optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/Project/'),
    {
        pk: 'id',
        column: [
            { label: t('project.name'), prop: 'name', align: 'center', operatorPlaceholder: t('Fuzzy query'), operator: 'LIKE', sortable: false },
            {
                label: t('project.is_stop'),
                prop: 'is_stop',
                align: 'center',
                render: 'switch',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('project.is_stop 0'), '1': t('project.is_stop 1') },
            },
            {
                label: t('project.wind_speed'),
                prop: 'wind_speed',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('project.rainfall'),
                prop: 'rainfall',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: {
                    '0': t('project.rainfall 0'),
                    '1': t('project.rainfall 1'),
                    '2': t('project.rainfall 2'),
                    '3': t('project.rainfall 3'),
                },
            },
            {
                label: t('project.create_time'),
                prop: 'create_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('project.update_time'),
                prop: 'update_time',
                align: 'center',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                width: 160,
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, 'is_stop'],
    },
    {
        defaultItems: { is_stop: '1', rainfall: '0' },
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getData()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<style scoped lang="scss">
.project-box {
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
