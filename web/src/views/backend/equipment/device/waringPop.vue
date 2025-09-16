<template>
    <el-dialog
        :model-value="['Warn'].includes(baTable.form.operate!)"
        title="告警列表"
        width="800px"
        @close="baTable.toggleForm"
        class="warning-list-dialog"
        :loading="loading"
        align-center
    >
        <!-- 搜索和筛选区域 -->
        <div class="search-filter-section">
            <div class="search-input">
                <el-input v-model="searchKeyword" placeholder="搜索" :prefix-icon="Search" class="search-field" @input="handleSearch" />
            </div>
            <div class="date-range-input">
                <el-date-picker
                    v-model="dateRange"
                    type="daterange"
                    range-separator="-"
                    start-placeholder="开始时间"
                    end-placeholder="结束时间"
                    value-format="YYYY-MM-DD HH:mm:ss"
                    class="date-picker"
                />
            </div>
        </div>

        <!-- 告警卡片列表 -->
        <div class="warning-cards-container">
            <!-- 有数据时显示卡片列表 -->
            <div v-if="warnings.length > 0" class="warning-cards-grid">
                <div v-for="(item, index) in warnings" :key="index" class="warning-card">
                    <!-- 警告等级标签 -->
                    <div :class="[`warning-level-tag-${item.level}`, 'warning-level-tag']">
                        {{ item.level == '0' ? '通知' : item.level == '1' ? '提醒' : '警告' }}
                    </div>

                    <!-- 警告内容 -->
                    <div class="warning-content">
                        {{ item.message }}
                    </div>

                    <!-- 时间信息 -->
                    <div class="time-info">
                        <div class="time-item">
                            <div class="time-label">开始时间</div>
                            <div class="time-value">{{ timeFormat(item.create_time) }}</div>
                        </div>
                        <!-- <div class="time-item">
                            <div class="time-label">结束时间</div>
                            <div class="time-value">{{ warning.endTime }}</div>
                        </div> -->
                    </div>

                    <!-- 解决方案 -->
                    <!-- <div class="solution-info">
                        <div class="solution-label">解决方案</div>
                        <div class="solution-content">{{ warning.solution }}</div>
                    </div> -->
                </div>
            </div>

            <!-- 无数据时显示空状态 -->
            <EmptyState v-else icon="DocumentRemove" title="暂无告警信息" :description="getEmptyDescription()" :icon-size="48" icon-color="#d9d9d9" />
        </div>
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, computed, watch, inject } from 'vue'
import { Search } from '@element-plus/icons-vue'
import EmptyState from '/@/components/emptyState/index.vue'
import type baTableClass from '/@/utils/baTable'
import { baTableApi } from '/@/api/common'
import { disposition } from '/@/config/disposition'
import { timeFormat } from '/@/utils/common'

const baTable = inject('baTable') as baTableClass

const api = new baTableApi('/admin/hmscenter/')

watch(
    () => baTable.form.operate,
    (newVal: string | undefined) => {
        if (newVal === 'Warn') {
            getWarningList()
        }
    }
)

// 获取告警列表
const getWarningList = async () => {
    loading.value = true
    const res = await api.index({
        sn: disposition.djiDock.gateway_sn,
    })
    warnings.value = res.data.list
    loading.value = false
}

const searchKeyword = ref('')
const dateRange = ref<[]>([])
const loading = ref(false)

watch(dateRange, (newVal: any) => {
    searchWarning()
})

const time = ref<any>(null)

const warnings = ref<any[]>([])

// 处理搜索
const handleSearch = (value: string) => {
    clearTimeout(time.value)
    time.value = setTimeout(() => {
        searchWarning()
    }, 300)
}

// 搜索接口
const searchWarning = async () => {
    let search = []
    if (searchKeyword.value) {
        search.push({
            field: 'message',
            val: searchKeyword.value,
            operator: 'LIKE',
        })
    }
    if (dateRange.value && dateRange.value.length > 0) {
        search.push({
            field: 'create_time',
            val: dateRange.value.join(','),
            operator: 'RANGE',
            render: 'datetime',
        })
    }
    const res = await api.index({
        search,
    })
    warnings.value = res.data.list
}

// 获取空状态描述文字
const getEmptyDescription = () => {
    if (searchKeyword.value) {
        return `未找到包含"${searchKeyword.value}"的告警信息`
    }
    return '当前设备暂无告警信息'
}
</script>

<style scoped>
.warning-list-dialog {
    border-radius: 24px;
}

.search-filter-section {
    display: flex;
    gap: 16px;
    margin-bottom: 32px;
    padding: 0 16px;
}

.search-input,
.date-range-input {
    flex: 1;
}

.warning-cards-container {
    max-height: 600px;
    overflow-y: auto;
    padding: 0 16px;
}

.warning-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.warning-card {
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 16px;
    padding: 20px;
    background: white;
    position: relative;
}

.warning-level-tag {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    margin-bottom: 16px;
}

.warning-level-tag-0 {
    border: 1px solid #909399;
    color: #909399;
}

.warning-level-tag-1 {
    border: 1px solid #e6a23c;
    color: #e6a23c;
}

.warning-level-tag-2 {
    border: 1px solid #f56c6c;
    color: #f56c6c;
}

.warning-content {
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 500;
    color: #000;
    line-height: 1.5;
    margin-bottom: 24px;
}

.time-info {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.time-item {
    flex: 1;
}

.time-label,
.solution-label {
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    color: rgba(0, 0, 0, 0.4);
    margin-bottom: 4px;
}

.time-value,
.solution-content {
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    color: #000;
}

.solution-info {
    margin-top: 16px;
}

/* 滚动条样式 */
.warning-cards-container::-webkit-scrollbar {
    display: none;
}
</style>
