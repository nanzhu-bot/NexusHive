<template>
    <div class="aircraft-market" v-loading="loading">
        <!-- 搜索区域 -->
        <div class="search-section">
            <div class="search-input-wrapper">
                <el-input v-model="searchKeyword" placeholder="搜索" style="width: 400px" clearable size="large" :prefix-icon="Search" />
            </div>
        </div>

        <!-- 飞行器卡片网格 -->
        <div class="aircraft-grid" v-if="loads.length > 0">
            <div v-for="load in loads" :key="load.id" class="aircraft-card" @click="handleAircraftClick(load)">
                <!-- 飞行器图片区域 -->
                <div class="aircraft-image">
                    <img :src="load.pic" :alt="load.name" class="image-placeholder" />
                </div>

                <!-- 飞行器信息区域 -->
                <div class="aircraft-info">
                    <div class="aircraft-details">
                        <div class="aircraft-name">{{ load.name }}</div>
                        <div class="aircraft-specs">固件版本{{ load.firmware_version }}</div>
                    </div>
                    <div class="aircraft-category-tag">
                        {{ load.scenarios }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 空数据提示 -->
        <EmptyState
            v-else
            :icon="searchKeyword ? 'Search' : 'Box'"
            :title="searchKeyword ? '未找到相关负载' : '暂无负载数据'"
            :description="emptyDescription"
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
import { ArrowLeft, Search } from '@element-plus/icons-vue'
import EmptyState from '/@/components/emptyState/index.vue'
import { baTableApi } from '/@/api/common'
import { useMedia } from '/@/stores/media'
const router = useRouter()
const searchKeyword = ref('')
const categoryFilter = ref('')

const api = new baTableApi('/admin/equipment.load/')
const mediaStore = useMedia()
const loading = ref(false)

const time = ref<any>(null)

watch(searchKeyword, (newVal: any) => {
    clearTimeout(time.value)
    time.value = setTimeout(() => {
        handleSearch()
    }, 300)
})

interface Load {
    id: number
    name: string
    scenarios: string
    pic: string
    create_time: number
    update_time: number
    content: string
    firmware_version: string
}

// 飞行器数据
const loads = ref<Load[]>([])

// 处理搜索
const handleSearch = async () => {
    loading.value = true
    let search = []
    if (searchKeyword.value) {
        search.push({
            field: 'name',
            val: searchKeyword.value,
            operator: 'LIKE',
        })
    }
    const res = await api.index({ search })
    loads.value = res.data.list
    loads.value.forEach((item: any) => {
        item.pic = mediaStore.uploadApi + item.pic
    })
    loading.value = false
}

// 空数据描述
const emptyDescription = computed(() => {
    if (searchKeyword.value) {
        return `没有找到包含"${searchKeyword.value}"的飞行器，请尝试其他关键词`
    }
    return '当前飞行器市场暂无可用产品，请稍后再试'
})

// 清除搜索
const clearSearch = () => {
    searchKeyword.value = ''
    categoryFilter.value = ''
}

// 处理飞行器点击
const handleAircraftClick = (load: any) => {
    router.push(`/admin/equipment/load/detail?id=${load.id}`)
}

onMounted(() => {
    handleSearch()
})
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    box-shadow: none !important;
    background: #f7f7f7;
    border-radius: 12px;
}

.page-header {
    display: flex;
    align-items: center;

    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: #000000;
        margin: 0;
    }
}

.aircraft-market {
    padding: 15px;
    background: #fff;
    height: 100%;
}

.search-input-wrapper {
    width: 400px;
    margin-top: 20px;
}

.search-input {
    width: 100%;
}

.aircraft-grid {
    padding: 28px 0;
    display: grid;
    grid-template-columns: repeat(6);
    gap: 16px;
}

.aircraft-card {
    width: 100%;
    height: 231px;
    border-radius: 10px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.aircraft-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: rgba(0, 0, 0, 0.2);
}

.aircraft-image {
    width: 100%;
    height: 164px;
    position: relative;
}

.image-placeholder {
    width: 100%;
    height: 100%;
}

.aircraft-info {
    height: 61px;
    background: white;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding: 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.aircraft-details {
    flex: 1;
}

.aircraft-name {
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #000;
    margin-bottom: 4px;
    line-height: 1.2;
}

.aircraft-specs {
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.4);
    line-height: 1.2;
}

.aircraft-category-tag {
    width: 5em;
    height: 21px;
    border: 1px solid #00386d;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #00386d;
    padding: 0 4px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 响应式设计 */
@media screen and (max-width: 1920px) {
    .aircraft-grid {
        grid-template-columns: repeat(auto-fit, 280px);
        justify-content: flex-start;
    }
}

@media screen and (max-width: 768px) {
    .aircraft-grid {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        padding: 16px;
    }
}
:deep(.el-input__icon) {
    color: #000000;
}
</style>
