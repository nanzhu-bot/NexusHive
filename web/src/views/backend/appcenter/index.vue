<template>
    <div class="analytics-view" v-loading="loading">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">应用中心</h1>
        </div>
        <!-- 搜索区域 -->
        <div class="search-section">
            <el-input v-model="searchKeyword" placeholder="搜索应用" style="width: 400px" clearable size="large" :prefix-icon="Search"> </el-input>
        </div>

        <!-- 应用网格 -->
        <div v-if="appList.length > 0" class="app-grid">
            <div v-for="app in appList" :key="app.id" class="app-card" @click="handleAppClick(app)">
                <div class="app-icon">
                    <el-image style="width: 64px; height: 64px; border-radius: 12px" :src="app.pic" fit="fill" />
                </div>
                <div class="app-info">
                    <div class="app-name">{{ app.name }}</div>
                    <div class="app-description">{{ app.introduction }}</div>
                </div>
            </div>
        </div>

        <!-- 空数据提示 -->
        <EmptyState
            v-else
            :icon="searchKeyword ? 'Search' : 'Box'"
            :title="searchKeyword ? '未找到相关应用' : '暂无应用数据'"
            :description="searchKeyword ? `没有找到包含“${searchKeyword}”的应用，请尝试其他关键词` : '当前应用中心暂无可用应用，请稍后再试'"
            :show-action="!!searchKeyword"
            action-text="清除搜索"
            @action="clearSearch"
        />
    </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import { Search, Download } from '@element-plus/icons-vue'
import EmptyState from '/@/components/emptyState/index.vue'
import { baTableApi } from '/@/api/common'
import { useMedia } from '/@/stores/media'
const router = useRouter()
const api = new baTableApi('/admin/Appcenter/')

interface App {
    id: number
    name: string
    create_time: number
    status: number
    update_time: number
    pic: string
    introduction: string
}

const searchKeyword = ref('')
const loading = ref(false)

const mediaStore = useMedia()

const time = ref<any>(null)

watch(searchKeyword, (newVal: any) => {
    clearTimeout(time.value)
    time.value = setTimeout(() => {
        getAppList()
    }, 300)
})

// 应用数据
const appList = ref<App[]>([])

// 处理搜索
const getAppList = async () => {
    loading.value = true
    let search = []
    if (searchKeyword.value) {
        search.push({
            field: 'name',
            val: searchKeyword.value,
            operator: 'LIKE',
        })
    }
    const res = await api.index({
        search,
    })
    appList.value = res.data.list
    appList.value.forEach((item: any) => {
        item.pic = mediaStore.uploadApi + item.pic
    })
    loading.value = false
}

// 处理应用点击
const handleAppClick = (app: any) => {
    // 跳转到应用详情页面
    router.push(`/admin/appcenter/detail?id=${app.id}`)
}

// 清除搜索
const clearSearch = () => {
    searchKeyword.value = ''
}

onMounted(() => {
    api.index().then((res) => {
        appList.value = [...res.data.list]
        appList.value.forEach((item: any) => {
            item.pic = mediaStore.uploadApi + item.pic
        })
    })
})
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    box-shadow: none !important;
    background: #f7f7f7;
    border-radius: 12px;
}

.analytics-view {
    padding: 15px;
    background-color: #fff;
    height: 100%;
}

.page-header {
    margin-bottom: 24px;

    .page-title {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 0;
    }
}

/* 搜索区域 */
.search-section {
    margin-bottom: 32px;
}

.search-input {
    width: 300px;
}

.search-icon {
    color: #999;
}

/* 应用网格 */
.app-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.app-card {
    flex: 0 0 calc(20% - 13px);
    display: flex;
    align-items: center;
    padding: 16px;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.app-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: rgba(0, 0, 0, 0.2);
}

.app-icon {
    width: 60px;
    height: 60px;
    margin-right: 16px;
    flex-shrink: 0;
}

.app-icon img {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    object-fit: cover;
}

.app-info {
    flex: 1;
    margin-right: 16px;
}

.app-name {
    font-size: 16px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}

.app-description {
    font-size: 14px;
    color: #666;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.app-action {
    flex-shrink: 0;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .app-grid {
        grid-template-columns: 1fr;
    }

    .search-input {
        width: 100%;
    }

    .app-card {
        padding: 12px;
    }

    .app-icon {
        width: 50px;
        height: 50px;
        margin-right: 12px;
    }

    .app-name {
        font-size: 14px;
    }

    .app-description {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .analytics-view {
        padding: 16px;
    }

    .app-card {
        flex-direction: column;
        text-align: center;
    }

    .app-icon {
        margin-right: 0;
        margin-bottom: 12px;
    }

    .app-info {
        margin-right: 0;
        margin-bottom: 12px;
    }
}

:deep(.el-input__icon) {
    color: #000000;
}
</style>
