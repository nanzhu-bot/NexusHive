<template>
    <div class="pagination-container">
        <div class="pagination-info" v-if="showTotal">
            共 <span class="total-count">{{ total }}</span> 条记录
        </div>
        <el-pagination
            v-model:current-page="currentPageSync"
            v-model:page-size="pageSizeSync"
            :page-sizes="pageSizes"
            :layout="layout"
            :total="total"
            @size-change="handleSizeChange"
            @current-change="handleCurrentChange"
            :background="background"
            :small="small"
            :hide-on-single-page="hideOnSinglePage"
            :pager-count="pagerCount"
            :popper-class="popperClass"
            :disabled="disabled"
        >
        </el-pagination>
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
    // 当前页码
    currentPage: {
        type: Number,
        default: 1,
    },
    // 每页条数
    pageSize: {
        type: Number,
        default: 10,
    },
    // 总条数
    total: {
        type: Number,
        default: 0,
    },
    // 可选的每页条数
    pageSizes: {
        type: Array,
        default: () => [10, 20, 50, 100],
    },
    // 布局
    layout: {
        type: String,
        default: 'total, sizes, prev, pager, next, jumper',
    },
    // 是否显示背景色
    background: {
        type: Boolean,
        default: true,
    },
    // 是否使用小型分页
    small: {
        type: Boolean,
        default: false,
    },
    // 只有一页时是否隐藏
    hideOnSinglePage: {
        type: Boolean,
        default: false,
    },
    // 是否显示总数
    showTotal: {
        type: Boolean,
        default: true,
    },
    // 页码按钮的数量，当总页数超过该值时会折叠
    pagerCount: {
        type: Number,
        default: 7,
    },
    // 弹出框的类名
    popperClass: {
        type: String,
        default: '',
    },
    // 是否禁用
    disabled: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['update:currentPage', 'update:pageSize', 'pagination-change'])

// 内部状态，用于双向绑定
const currentPageSync = computed({
    get: () => props.currentPage,
    set: (val) => emit('update:currentPage', val),
})

const pageSizeSync = computed({
    get: () => props.pageSize,
    set: (val) => emit('update:pageSize', val),
})

// 处理页码变化
const handleCurrentChange = (val: number) => {
    emit('pagination-change', {
        currentPage: val,
        pageSize: pageSizeSync.value,
    })
}

// 处理每页条数变化
const handleSizeChange = (val: number) => {
    emit('pagination-change', {
        currentPage: 1, // 切换每页条数时，通常重置为第一页
        pageSize: val,
    })
}
</script>

<style scoped>
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding: 10px 0;
}

.pagination-info {
    margin-right: 16px;
    font-size: 14px;
    color: #606266;
}

.total-count {
    font-weight: bold;
    color: #409eff;
}
</style>
