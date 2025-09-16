<template>
    <div class="records-view">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">飞行记录</h1>
        </div>

        <!-- 文件夹卡片区域 -->
        <div class="folder-cards">
            <div
                v-for="(folder, index) in folderList"
                :key="index"
                class="folder-card"
                :class="{ active: selectedFolder === index }"
                @click="selectFolder(index)"
            >
                <div class="folder-icon">
                    <img src="/src/assets/wjj.png" alt="" style="width: 40px; height: 40px" />
                </div>
                <div class="folder-info">
                    <div class="folder-name">{{ folder.nickname }}</div>
                    <!-- <div class="folder-count">{{ folder.count }}条飞行记录</div> -->
                </div>
            </div>
        </div>

        <!-- 自定义按钮请使用插槽，甚至公共搜索也可以使用具名插槽渲染，参见文档 -->
        <TableSift
            :buttons="['daterange', 'add']"
            quick-search-placeholder="飞行记录搜索"
            btn-text="导出全部飞行记录"
            @onBtnClick="handleExport"
            search-key="airline.name"
        />

        <!-- 表格 -->
        <div class="view-table">
            <!-- 要使用 el-table 组件原有的属性，直接加在 Table 标签上即可 -->
            <Table ref="tableRef">
                <template #actual_time>
                    <el-table-column prop="actual_time" label="实际时间">
                        <template #default="scope">
                            <div>{{ getActualTime(scope.row) }}</div>
                        </template>
                    </el-table-column>
                </template>

                <!-- 执行状态 -->
                <template #execute_status>
                    <el-table-column prop="status" label="执行状态" width="160">
                        <template #default="scope">
                            <div class="execute-status">
                                <div
                                    class="execute-status-icon"
                                    :class="scope.row['status'] === 'ok' ? 'success' : scope.row['status'] === 'failed' ? 'error' : 'info'"
                                ></div>
                                <div>{{ execute_status[scope.row['status'] as keyof typeof execute_status] }}</div>
                                <div>{{ `(${scope.row['now_point']}/${scope.row['total_point']})` }}</div>
                                <!-- 原因 -->
                                <el-tooltip
                                    v-if="scope.row['status'] !== 'ok' && scope.row['error_code']"
                                    effect="dark"
                                    :content="errorCode[scope.row['error_code'] as keyof typeof errorCode]"
                                    placement="top-start"
                                    style="cursor: pointer"
                                >
                                    <el-icon color="#E6A23C"><Warning /></el-icon>
                                </el-tooltip>
                            </div>
                        </template>
                    </el-table-column>
                </template>

                <!-- 媒体文件 -->
                <template #media_upload>
                    <el-table-column prop="media_upload" label="媒体文件">
                        <template #default="scope">
                            <div class="media-upload">
                                <div
                                    class="media-upload-icon"
                                    :class="
                                        scope.row['media_total'] == 0
                                            ? 'info'
                                            : scope.row['media_total'] == scope.row['media_upload_num']
                                              ? 'success'
                                              : 'error'
                                    "
                                ></div>
                                <!-- 状态 -->
                                <div>{{ getMediaUploadStatus(scope.row) }}</div>
                                <!-- 媒体文件数量 -->
                                <div v-if="scope.row['media_total'] > 0">
                                    {{ `(${scope.row['media_upload_num'] || 0}/${scope.row['media_total']})` }}
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                </template>
            </Table>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, useTemplateRef, provide, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import TableSift from '/@/components/tableSift/index.vue'
import Table from '/@/components/table/index1.vue'
import { defaultOptButtons } from '/@/components/table'
import baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { execute_status } from '../flightSpace/type/description'
import { Warning } from '@element-plus/icons-vue'
import { errorCode } from '/@/config/eorr'
import { ElMessage } from 'element-plus'

interface Folder {
    id: number
    nickname: string
    equipment_id: string
}

const { t } = useI18n()
const tableRef = useTemplateRef('tableRef')

const api = new baTableApi('/admin/Equipment/')

// 文件夹数据
const folderList = ref<Folder[]>([])

const selectedFolder = ref(0)

watch(selectedFolder, (newVal: number) => {
    if (newVal !== null) {
        baTable.comSearch.form.equipment_id = folderList.value[newVal].id
        baTable.onTableAction('com-search', {})
    }
})

/**
 * baTable 内包含了表格的所有数据且数据具备响应性，然后通过 provide 注入给了后代组件
 */
const baTable = new baTableClass(
    new baTableApi('/admin/Flighttask/'),
    {
        pk: 'id',
        column: [
            // 实际时间
            { render: 'slot', slotName: 'actual_time' },
            // 执行状态
            { render: 'slot', slotName: 'execute_status' },
            {
                label: t('flighttask.task_type'),
                prop: 'task_type',
                align: 'center',
                render: 'tag',
                operator: 'eq',
                sortable: false,
                replaceValue: { '0': t('flighttask.task_type 0'), '1': t('flighttask.task_type 1'), '2': t('flighttask.task_type 2') },
            },
            {
                label: '计划名称',
                prop: 'name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                operator: 'LIKE',
            },
            {
                label: t('flighttask.airline__name'),
                prop: 'airline.name',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            {
                label: t('flighttask.equipment__nickname'),
                prop: 'equipment.nickname',
                align: 'center',
                operatorPlaceholder: t('Fuzzy query'),
                render: 'tags',
                operator: 'LIKE',
            },
            { label: '创建人', prop: 'admin.nickname', align: 'center', operator: 'RANGE', sortable: false },
            // 媒体上传
            { render: 'slot', slotName: 'media_upload' },
        ],
        dblClickNotEditColumn: [undefined],
    },
    {
        defaultItems: { task_type: '0', rth_mode: '0', out_of_control_action: '0', exit_wayline_when_rc_lost: '0', wayline_precision_type: '0' },
    }
)

provide('baTable', baTable)

// 选择文件夹
const selectFolder = (index: number) => {
    selectedFolder.value = index
}

// 导出全部飞行记录
const handleExport = async (searchForm: any) => {
    console.log(searchForm)

    if (searchForm.dateRange.length == 0) {
        ElMessage.warning('请选择时间范围')
        return
    }

    let create_time = new Date(searchForm.dateRange[0]).getTime() / 1000 + '-' + new Date(searchForm.dateRange[1]).getTime() / 1000
    const res = await new baTableApi('/admin/Flighttask/').export({
        create_time: create_time,
    })
    window.open(res.data.download_url)
}

// 获取文件夹数据
const getFolderList = async () => {
    const res = await api.index()
    folderList.value = res.data.list
    if (folderList.value.length > 0) {
        initData()
    }
}

// 初始化
const initData = () => {
    baTable.comSearch.form.equipment_id = folderList.value[selectedFolder.value].id
    baTable.table.filter!.search = [{ field: 'equipment_id', val: folderList.value[selectedFolder.value].id, operator: 'eq' }]
    baTable.getData()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
}

const getMediaUploadStatus = (item: any) => {
    if (item.media_total == 0) return '无媒体文件'
    if (item.media_total == item.media_upload_num) return '上传结束'
    return '上传中'
}

const getActualTime = (item: any) => {
    // 开始时间
    let start_time = new Date(item.execute_time).getTime() / 1000
    // 结束时间
    let end_time = new Date(item.end_time).getTime() / 1000

    // 实际用时
    let actual_time = end_time - start_time

    if (actual_time < 0) return '0s'
    if (actual_time == 0) return '0分0秒'
    // 计算小时
    let hours = Math.floor(actual_time / 3600)
    // 计算分钟
    let minutes = Math.floor((actual_time % 3600) / 60)
    // 计算秒
    let seconds = actual_time % 60

    if (hours > 0) return `${hours}小时${minutes}分${seconds}秒`
    if (minutes > 0) return `${minutes}分${seconds}秒`
    return `${seconds}秒`
}

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    getFolderList()
})
</script>

<style scoped>
:deep(.el-input__wrapper) {
    box-shadow: none !important;
    background: #f7f7f7;
    border-radius: 12px;
}

.records-view {
    background-color: #fff;
    height: 100%;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.view-table {
    flex: 1;
    overflow: auto;

    scrollbar-width: thin; /* auto | thin | none */
    scrollbar-color: #999 transparent; /* 滑块颜色 轨道颜色 */

    /* 隐藏滚动条箭头 */
    &::-webkit-scrollbar-button {
        display: none;
    }
}

.page-header {
    padding: 15px;
}

.page-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

/* 文件夹卡片样式 */
.folder-cards {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding: 0 15px;
}

.folder-card {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 160px;
}

.folder-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: rgba(0, 0, 0, 0.2);
}

.folder-card.active {
    background: #e3f2fd;
}

.folder-icon {
    margin-right: 12px;
}

.folder-info {
    flex: 1;
}

.folder-name {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 4px;
}

.folder-count {
    font-size: 12px;
    color: #666;
}

.folder-settings {
    margin-left: 8px;
}

/* 工具栏样式 */
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.search-section {
    display: flex;
    gap: 16px;
    align-items: center;
}

.search-input {
    width: 200px;
}

.date-picker {
    width: 280px;
}

.export-btn {
    background: #00386d;
    border-color: #00386d;
    font-size: 14px;
}

/* 表格样式 */
.table-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 16px;
}

.flight-table {
    background: white;
}

/* 表格样式重置 */
:deep(.el-table) {
    border: none;
}

:deep(.el-table th) {
    background-color: #fafafa;
    color: #666;
    font-weight: 500;
    border-bottom: 1px solid #f0f0f0;
    padding: 12px 0;
}

:deep(.el-table td) {
    border-bottom: 1px solid #f0f0f0;
    padding: 12px 0;
}

:deep(.el-table tr:hover > td) {
    background-color: #fafafa;
}

:deep(.el-table::before) {
    display: none;
}

.execute-status {
    display: flex;
    align-items: center;
    gap: 4px;

    .execute-status-icon {
        width: 10px;
        height: 10px;
        border-radius: 50%;

        &.success {
            background: #2ba471;
        }

        &.error {
            background: #f56c6c;
        }

        &.info {
            background: #e6a23c;
        }
    }
}
.media-upload {
    display: flex;
    align-items: center;
    gap: 4px;

    .media-upload-icon {
        width: 10px;
        height: 10px;
        border-radius: 50%;

        &.success {
            background: #2ba471;
        }

        &.error {
            background: #e6a23c;
        }

        &.info {
            background: #c5c8ce;
        }
    }
}
</style>
