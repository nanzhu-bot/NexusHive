<template>
    <div class="app-detail-view">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">应用详情</h1>
        </div>
        <!-- 返回按钮 -->
        <div class="back-section">
            <el-button type="text" :icon="ArrowLeft" @click="goBack" class="back-btn"> 返回 </el-button>
        </div>

        <!-- 应用详情主体 -->
        <div class="app-detail-content">
            <!-- 应用图片 -->
            <div class="app-image">
                <img :src="appDetail.pic" :alt="appDetail.name" />
            </div>

            <!-- 应用信息 -->
            <div class="app-info">
                <h1 class="app-title">{{ appDetail.name }}</h1>
                <p class="app-description">{{ appDetail.introduction }}</p>
            </div>
        </div>

        <!-- 功能介绍 -->
        <div class="features-section">
            <el-tabs v-model="activeTab" class="feature-tabs">
                <el-tab-pane label="功能介绍" name="features">
                    <div class="features-grid" v-html="appDetail.content"></div>
                </el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ArrowLeft } from '@element-plus/icons-vue'
import { ElMessage } from 'element-plus'
import { baTableApi } from '/@/api/common'
import { useMedia } from '/@/stores/media'
const api = new baTableApi('/admin/Appcenter/')

interface AppFeature {
    id: number
    title: string
    description: string
    image: string
}

interface AppDetail {
    id: number
    name: string
    create_time: number
    status: number
    update_time: number
    pic: string
    introduction: string
    content: string
}

const router = useRouter()
const route = useRoute()

const mediaStore = useMedia()

// 选项卡状态
const activeTab = ref('features')

// 应用详情数据
const appDetail = ref<AppDetail>({
    id: 0,
    name: '',
    create_time: 0,
    status: 0,
    update_time: 0,
    pic: '',
    introduction: '',
    content: '',
})

// 返回上一页
const goBack = () => {
    router.back()
}

// 根据路由参数加载应用详情
onMounted(() => {
    if (route.query.id) {
        getAppDetail(route.query.id)
    }
})

// 获取详情
const getAppDetail = async (id: any) => {
    const res = await api.edit({ id })
    appDetail.value = res.data.row
    appDetail.value.pic = mediaStore.uploadApi + appDetail.value.pic
}
</script>

<style scoped lang="scss">
.app-detail-view {
    padding: 15px;
    background: #fff;
    height: 100%;
}

.page-header {
    margin-bottom: 24px;
}

.page-title {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    margin: 0;
}

/* 返回按钮 */
.back-section {
    margin-bottom: 24px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.back-btn {
    font-size: 16px;
    color: #333;
    padding: 0;
}

.back-btn:hover {
    color: #409eff;
}

/* 应用详情主体 */
.app-detail-content {
    display: flex;
    gap: 32px;
    margin-bottom: 48px;
    align-items: flex-start;
}

.app-image {
    flex-shrink: 0;
    width: 400px;
    height: 300px;
    border-radius: 12px;
    overflow: hidden;
    background: #f5f5f5;
}

.app-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.app-info {
    flex: 1;
}

.app-title {
    font-size: 32px;
    font-weight: 600;
    color: #333;
    margin: 0 0 24px 0;
}

.app-description {
    font-size: 16px;
    line-height: 1.6;
    color: #666;
    margin: 0;
}

/* 功能介绍 */
.features-section {
    margin-top: 48px;
}

.feature-tabs {
    --el-tabs-header-height: 48px;
}

:deep(.el-tabs__header) {
    margin-bottom: 24px;
}

:deep(.el-tabs__nav-wrap::after) {
    height: 1px;
    background-color: #e4e7ed;
}

:deep(.el-tabs__item) {
    font-size: 16px;
    font-weight: 500;
    color: #606266;
    padding: 0 20px;
}

:deep(.el-tabs__item.is-active) {
    color: #409eff;
    font-weight: 600;
}

/* 使用说明样式 */
.instructions-content {
    padding: 20px 0;
}

.instruction-item {
    margin-bottom: 32px;
}

.instruction-item h3 {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin: 0 0 16px 0;
}

.instruction-item ol,
.instruction-item ul {
    margin: 0;
    padding-left: 24px;
}

.instruction-item li {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 8px;
}

/* 版本历史样式 */
.versions-content {
    padding: 20px 0;
}

.version-item {
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #409eff;
}

.version-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.version-number {
    font-size: 16px;
    font-weight: 600;
    color: #409eff;
}

.version-date {
    font-size: 14px;
    color: #909399;
}

.version-changes {
    margin: 0;
    padding-left: 20px;
}

.version-changes li {
    font-size: 14px;
    color: #666;
    line-height: 1.6;
    margin-bottom: 6px;
}

/* 响应式设计 */
@media (max-width: 768px) {
    .app-detail-view {
        padding: 16px;
    }

    .app-detail-content {
        flex-direction: column;
        gap: 24px;
    }

    .app-image {
        width: 100%;
        height: 250px;
    }

    .app-title {
        font-size: 24px;
    }

    .app-description {
        font-size: 14px;
    }

    .features-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .section-title {
        font-size: 20px;
    }
}

@media (max-width: 480px) {
    .app-detail-content {
        gap: 16px;
    }

    .app-image {
        height: 200px;
    }

    .app-title {
        font-size: 20px;
    }

    .feature-info {
        padding: 16px;
    }

    .feature-title {
        font-size: 16px;
    }
}
</style>
