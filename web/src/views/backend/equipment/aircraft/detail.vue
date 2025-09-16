<template>
    <div class="aircraft-detail">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">飞行器详情</h1>
        </div>
        <!-- 返回按钮 -->
        <div class="back-section">
            <div class="back-button" @click="goBack">
                <el-icon class="back-icon">
                    <ArrowLeft />
                </el-icon>
                <span>返回</span>
            </div>
        </div>

        <!-- 主要内容区域 -->
        <div class="main-content">
            <!-- 产品详情区域 -->
            <div class="product-section">
                <!-- 产品图片 -->
                <div class="product-image">
                    <img :src="aircraftDetail.pic" alt="DJI FlyCart 30" />
                </div>

                <!-- 产品信息 -->
                <div class="product-info">
                    <div class="product-header">
                        <div class="product-tags">
                            <span class="tag">{{ aircraftDetail.scenarios }}</span>
                        </div>
                        <h1 class="product-title">{{ aircraftDetail.name }}</h1>
                    </div>

                    <div class="product-version">固件版本：{{ aircraftDetail.firmware_version }}</div>

                    <div class="product-description">
                        <div v-html="aircraftDetail.content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { ElMessage } from 'element-plus'
import { ArrowLeft } from '@element-plus/icons-vue'
import { baTableApi } from '/@/api/common'

const router = useRouter()
const route = useRoute()

const api = new baTableApi('/admin/equipment.Aircraft/')

const aircraftDetail = ref({
    id: 0,
    pic: '',
    name: '',
    scenarios: '',
    firmware_version: '',
    content: '',
})

// 返回上一页
const goBack = () => {
    router.back()
}

// 播放视频
const playVideo = (video: any) => {
    ElMessage.info(`播放视频: ${video.title}`)
}

onMounted(() => {
    if (route.query.id) {
        getAircraftDetail(route.query.id)
    }
})

// 获取详情
const getAircraftDetail = async (id: any) => {
    const res = await api.edit({ id })
    aircraftDetail.value = res.data.row
    aircraftDetail.value.pic = import.meta.env.VITE_AXIOS_BASE_URL + aircraftDetail.value.pic
}
</script>

<style scoped>
.page-header {
    padding: 15px;
    display: flex;
    align-items: center;

    .page-title {
        font-size: 24px;
        font-weight: 600;
        color: #000000;
        margin: 0;
    }
}
.aircraft-detail {
    background: white;
}

.back-section {
    height: 54px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.back-button {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: #000;
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    transition: color 0.3s ease;
}

.back-button:hover {
    color: #00386d;
}

.back-icon {
    font-size: 20px;
}

.main-content {
    padding: 15px;
    overflow: hidden;
}

.product-section {
    display: flex;
    gap: 100px;
    margin-bottom: 40px;
}

.product-image {
    width: 580px;
    height: 380px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 561.2px;
    height: 311.78px;
    object-fit: contain;
}

.product-info {
    flex: 1;
    padding-top: 89.5px;
}

.product-header {
    margin-bottom: 28px;
}

.product-tags {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.tag {
    max-width: 8em;
    padding: 2px 6px;
    border: 1px solid #00386d;
    border-radius: 4px;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #00386d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-title {
    font-size: 32px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 500;
    color: #000;
    margin: 0;
}

.product-version {
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.6);
    margin-bottom: 50px;
}

.product-description {
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #000;
    line-height: 1.6;
    max-width: 876px;
}

.case-title-section {
    width: 100%;
    height: 34px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    margin-bottom: 40px;
    position: relative;
}

.case-title {
    position: relative;
    display: inline-block;
}

.case-title span {
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #000;
}

.title-underline {
    width: 80px;
    height: 2px;
    background: #00386d;
    position: absolute;
    bottom: -1px;
    left: 0;
}

.video-cases {
    display: flex;
    gap: 40px;
    overflow-x: auto;
    padding-bottom: 20px;
}

.video-card {
    flex-shrink: 0;
    width: 375px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.video-card:hover {
    transform: translateY(-4px);
}

.video-thumbnail {
    width: 375px;
    height: 225px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
    position: relative;
    margin-bottom: 16px;
}

.video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.play-button {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 60px;
    height: 60px;
    background: rgba(0, 0, 0, 0.7);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}

.play-button:hover {
    background: rgba(0, 0, 0, 0.8);
}

.play-icon {
    font-size: 24px;
    color: white;
}

.video-title {
    font-size: 16px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #000;
}

/* 响应式设计 */
@media screen and (max-width: 1200px) {
    .product-section {
        flex-direction: column;
        gap: 40px;
    }

    .product-info {
        padding-top: 0;
    }

    .video-cases {
        gap: 20px;
    }

    .video-card {
        width: 300px;
    }

    .video-thumbnail {
        width: 300px;
        height: 180px;
    }
}

@media screen and (max-width: 768px) {
    .main-content {
        padding: 16px 20px;
    }

    .product-image {
        width: 100%;
        height: 300px;
    }

    .product-image img {
        width: 90%;
        height: 80%;
    }

    .video-card {
        width: 280px;
    }

    .video-thumbnail {
        width: 280px;
        height: 160px;
    }
}
</style>
