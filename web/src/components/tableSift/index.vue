<template>
    <div class="toolbar" :style="{ padding: padding || '15px' }">
        <el-button v-if="props.buttons.includes('add')" type="primary" class="new-btn" @click="showCreateDialog">{{ btnText }}</el-button>
        <div class="search-section" v-if="props.buttons.includes('search') || props.buttons.includes('daterange')">
            <el-input
                v-model="searchForm.keyword"
                :placeholder="quickSearchPlaceholder"
                :prefix-icon="Search"
                clearable
                size="large"
                class="search-input"
                v-if="props.buttons.includes('search')"
            />
            <el-date-picker
                v-model="searchForm.dateRange"
                :default-time="[new Date(2000, 1, 1, 0, 0, 0), new Date(2000, 1, 1, 23, 59, 59)]"
                type="daterange"
                range-separator="-"
                start-placeholder="开始时间"
                end-placeholder="结束时间"
                value-format="YYYY-MM-DD HH:mm:ss"
                size="large"
                class="date-picker"
                v-if="props.buttons.includes('daterange')"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, inject, reactive, watch } from 'vue'
import { Search } from '@element-plus/icons-vue'
import type baTableClass from '/@/utils/baTable'

interface Props {
    buttons: CustomizaOptButton[]
    quickSearchPlaceholder?: string
    btnText?: string
    padding?: string
    searchKey?: string
    isSearch?: boolean
}

const emit = defineEmits(['onBtnClick', 'onSearch'])

const props = withDefaults(defineProps<Props>(), {
    buttons: () => {
        return ['search', 'daterange', 'add']
    },
    quickSearchPlaceholder: '',
    btnText: '新建项目',
    isSearch: false,
})

const baTable = inject('baTable') as baTableClass

const time = ref<any>(null)

const searchForm = reactive({
    keyword: '',
    dateRange: [],
})

watch(
    () => searchForm.dateRange,
    (newVal: any) => {
        baTable.comSearch.form.create_time = newVal
        search()
    }
)

watch(
    () => searchForm.keyword,
    (newVal: any) => {
        baTable.comSearch.form[props.searchKey || 'name'] = newVal
        search()
    }
)

// 搜索
const search = () => {
    if (props.isSearch) {
        emit('onSearch')
        return
    }
    clearTimeout(time.value)
    time.value = setTimeout(() => {
        baTable.onTableAction('com-search', {})
    }, 300)
}

const showCreateDialog = () => {
    if (baTable) baTable.onTableHeaderAction('add', {})
    emit('onBtnClick', searchForm)
}
</script>

<style scoped lang="scss">
.toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
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
    width: 200px;
}

.new-btn {
    min-width: 128px;
    height: 48px;
    border-radius: 12px;
    background: #00386d;
    color: #fff;
    border: none;
    padding: 0 16px;
}

:deep(.el-input__wrapper) {
    box-shadow: none !important;
    background: #f7f7f7;
    border-radius: 12px;
}

:deep(.el-input__icon) {
    color: #000000;
}
</style>
