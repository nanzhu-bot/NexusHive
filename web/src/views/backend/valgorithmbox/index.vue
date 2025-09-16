<template>
    <div class="visual-algorithm-center" v-loading="loading">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">视觉算法中心</h1>
        </div>
        <!-- 搜索区域 -->
        <div class="search-section">
            <el-input v-model="searchKeyword" placeholder="搜索视觉算法" style="width: 400px" clearable size="large" :prefix-icon="Search" />
        </div>

        <!-- 算法卡片网格 -->
        <div class="algorithm-grid" v-if="algorithms.length > 0">
            <div v-for="algorithm in algorithms" :key="algorithm.id" class="algorithm-card" @click="handleAlgorithmClick(algorithm)">
                <div class="algorithm-image">
                    <!-- 算法预览图片区域 -->
                    <el-image :src="algorithm.avatar" fit="fill" style="width: 100%; height: 100%" />
                </div>
                <div class="algorithm-info">
                    <div class="algorithm-name">{{ algorithm.name }}</div>
                    <div class="algorithm-description">{{ algorithm.introduction }}</div>
                </div>
            </div>
        </div>

        <!-- 空数据提示 -->
        <EmptyState
            v-else
            :icon="searchKeyword ? 'Search' : 'DocumentRemove'"
            :title="searchKeyword ? '未找到相关算法' : '暂无算法数据'"
            :description="searchKeyword ? `没有找到包含“${searchKeyword}”的算法，请尝试其他关键词` : '当前视觉算法中心暂无可用算法，请稍后再试'"
            :show-action="!!searchKeyword"
            action-text="清除搜索"
            @action="clearSearch"
        />
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Search } from '@element-plus/icons-vue'
import EmptyState from '/@/components/emptyState/index.vue'
import { baTableApi } from '/@/api/common'
import { useMedia } from '/@/stores/media'

interface Algorithm {
    id: number
    name: string
    content: string
    avatar: string
    create_time: number
    update_time: number
    introduction: string
}

const router = useRouter()
const api = new baTableApi('/admin/Valgorithmbox/')
const mediaStore = useMedia()

// 搜索关键词
const searchKeyword = ref('')
const loading = ref(false)

const time = ref<any>(null)

watch(searchKeyword, (newVal: any) => {
    clearTimeout(time.value)
    time.value = setTimeout(() => {
        getAlgorithmList()
    }, 300)
})

// 算法数据
const algorithms = ref<Algorithm[]>([])

// 处理搜索
const getAlgorithmList = async () => {
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
    algorithms.value = res.data.list
    algorithms.value.forEach((item: any) => {
        item.avatar = mediaStore.uploadApi + item.avatar
    })
    loading.value = false
}

// 清除搜索
const clearSearch = () => {
    searchKeyword.value = ''
}

// 处理算法卡片点击
const handleAlgorithmClick = (algorithm: any) => {
    // 这里可以跳转到算法详情页面
    router.push(`/admin/valgorithmbox/detail?id=${algorithm.id}`)
}

onMounted(() => {
    api.index().then((res) => {
        algorithms.value = res.data.list
        algorithms.value.forEach((item: any) => {
            item.avatar = mediaStore.uploadApi + item.avatar
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

.visual-algorithm-center {
    background-color: #ffffff;
    height: 100%;
}

.page-header {
    padding: 15px 28px;
    display: flex;
    align-items: center;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #000000;
    margin: 0;
    font-family:
        'PingFang SC',
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        Roboto,
        sans-serif;
}

.search-section {
    padding: 0 28px;
    display: flex;
    align-items: center;
}

.algorithm-grid {
    padding: 28px;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 18px;
}

.algorithm-card {
    width: 100%;
    height: 231px;
    border-radius: 10px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.algorithm-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: rgba(0, 0, 0, 0.2);
}

.algorithm-image {
    width: 100%;
    height: 164px;
    background: #ba7171;
    position: relative;
}

.algorithm-info {
    width: 100%;
    height: 67px;
    background: white;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding: 10px;
    box-sizing: border-box;
}

.algorithm-name {
    font-size: 14px;
    font-weight: 400;
    color: #000000;
    font-family:
        'PingFang SC',
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        Roboto,
        sans-serif;
}

.algorithm-description {
    font-size: 12px;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.4);
    line-height: 1.4;
    font-family:
        'PingFang SC',
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        Roboto,
        sans-serif;
}

/* 响应式设计 */
@media screen and (max-width: 1600px) {
    .algorithm-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 24px;
    }
}

@media screen and (max-width: 1400px) {
    .algorithm-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }
}

@media screen and (max-width: 1200px) {
    .algorithm-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
}

@media screen and (max-width: 768px) {
    .page-header {
        padding: 20px 16px 0 16px;
    }

    .search-section {
        padding: 16px;
    }

    .search-input {
        width: 100%;
        max-width: 400px;
    }

    .algorithm-grid {
        padding: 16px;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

@media screen and (max-width: 480px) {
    .algorithm-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}
:deep(.el-input__icon) {
    color: #000000;
}
</style>
