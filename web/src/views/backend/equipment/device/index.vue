<template>
    <div class="equipment-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />
        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableSift :buttons="['search', 'daterange', 'add']" quick-search-placeholder="项目搜索" btn-text="添加设备" search-key="nickname" />
        <!-- 表格 -->
        <!-- 表格列有多种自定义渲染方式，比如自定义组件、具名插槽等，参见文档 -->
        <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
        <Table ref="tableRef">
            <template #project__name>
                <el-table-column label="项目名称">
                    <template #default="scope">
                        <el-link type="primary" @click="handleProjectName(scope.row)">{{ scope.row['project'].name }}</el-link>
                    </template>
                </el-table-column>
            </template>
        </Table>

        <!-- 表单 -->
        <!-- <PopupForm /> -->
        <!-- 警告 -->
        <WaringPop />
        <!-- 设置 -->
        <SettingPop />
        <!-- 实时视频 -->
        <LivePop />
        <!-- 新增设备 -->
        <AddDeviceDialog />
    </div>
</template>

<script setup lang="ts">
import { onMounted, provide, useTemplateRef, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { baTableApi } from '/@/api/common'
import { defaultOptButtons } from '/@/components/table'
import Table from '/@/components/table/index2.vue'
import baTableClass from '/@/utils/baTable'
import TableSift from '/@/components/tableSift/index.vue'
import WaringPop from './waringPop.vue'
import SettingPop from './settingPop.vue'
import LivePop from './livePop.vue'
import { disposition } from '/@/config/disposition'
import AddDeviceDialog from './AddDeviceDialog.vue'
import { useRouter } from 'vue-router'

defineOptions({
    name: 'equipment',
})

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')
const router = useRouter()
const livePop = ref<boolean>(false)
provide('livePop', livePop)

let optButtons: OptButton[] = defaultOptButtons(['edit', 'delete'])
// let optButtons: OptButton[] = defaultOptButtons([])

optButtons[0].display = (row: TableRow, field: TableColumn) => {
    return row.type === '3'
}

// 自定义一个新的按钮
let newButton: OptButton[] = [
    {
        render: 'tipButton',
        name: 'warn',
        title: '警告',
        type: 'warning',
        icon: 'el-icon-WarnTriangleFilled',
        // 自定义点击事件
        click: (row: TableRow, field: TableColumn) => {
            baTable.toggleForm('Warn', [row.sn])
            disposition.setDjiDockVideoId(row.sn)
        },
        display: (row: TableRow, field: TableColumn) => {
            return row.type === '3'
        },
    },
    {
        render: 'tipButton',
        name: 'setting',
        title: '设置',
        type: 'info',
        icon: 'el-icon-Setting',
        // 自定义点击事件 - 订阅MQTT消息
        click: async (row: TableRow, field: TableColumn) => {
            baTable.toggleForm('Setting', [row.sn])
            disposition.setDjiDockVideoId(row.sn)
        },
        display: (row: TableRow, field: TableColumn) => {
            return row.type === '3'
        },
    },
]

optButtons = newButton.concat(optButtons)

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/Equipment/'),
    {
        pk: 'id',
        column: [
            {
                label: t('equipment.model'),
                prop: 'model',
                align: 'left',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            {
                label: t('equipment.sn'),
                prop: 'sn',
                align: 'left',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            // {
            //     label: t('equipment.manufacturer'),
            //     prop: 'manufacturer',
            //     align: 'left',
            //     render: 'tag',
            //     operator: 'eq',
            //     sortable: false,
            //     replaceValue: { '0': t('equipment.manufacturer 0'), '1': t('equipment.manufacturer 1') },
            // },
            {
                label: t('equipment.nickname'),
                prop: 'nickname',
                align: 'left',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
                sortable: false,
            },
            // {
            //     label: t('equipment.project__name'),
            //     prop: 'project.name',
            //     align: 'left',
            //     operatorPlaceholder: t('Fuzzy query'),
            //     render: 'tags',
            //     operator: 'LIKE',
            // },
            {
                label: t('equipment.project__name'),
                render: 'slot',
                slotName: 'project__name',
            },

            {
                label: t('equipment.mode_code'),
                prop: 'mode_code',
                align: 'left',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: {
                    '0': t('equipment.mode_code 0'),
                    '1': t('equipment.mode_code 1'),
                    '2': t('equipment.mode_code 2'),
                    '3': t('equipment.mode_code 3'),
                    '4': t('equipment.mode_code 4'),
                    '5': t('equipment.mode_code 5'),
                },
            },
            {
                label: t('equipment.create_time'),
                prop: 'create_time',
                align: 'left',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            {
                label: t('equipment.update_time'),
                prop: 'update_time',
                align: 'left',
                render: 'datetime',
                operator: 'RANGE',
                sortable: 'custom',
                timeFormat: 'yyyy-mm-dd hh:MM:ss',
            },
            { label: t('Operate'), align: 'center', width: 180, render: 'buttons', buttons: optButtons, fixed: 'right' },
            // { label: '操作', render: 'slot', slotName: 'option', operator: 'LIKE' },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { manufacturer: '0', mode_code: '0' },
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
    setTimeout(() => {
        console.log(baTable.table.data)
    }, 1500)
})

const handleProjectName = (row: any) => {
    router.push(`/admin/flightSpace?id=${row.project_id}`)
}
</script>

<style scoped lang="scss">
.equipment-box {
    background: #fff;
    height: 100%;
}

.page-header {
    padding: 10px 15px;
    display: flex;
    align-items: center;

    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: #000000;
        margin: 0;
    }
}
</style>
