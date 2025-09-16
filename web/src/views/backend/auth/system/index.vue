<template>
    <div class="system-update-management">
        <!-- 页面标题 -->
        <div class="page-header">
            <h1 class="page-title">系统更新</h1>
        </div>
        <!-- 系统更新列表 -->
        <div class="update-container">
            <div class="update-grid">
                <div v-for="item in updateList" :key="item.id" class="update-card">
                    <!-- 无人机图片 -->
                    <div class="drone-image">
                        <img :src="item.image" :alt="item.name" />
                    </div>
                    <!-- 更新信息 -->
                    <div class="update-info">
                        <h3 class="drone-name">{{ item.name }}</h3>

                        <div class="version-info">
                            <div class="version-item current">
                                <span class="version-label">当前固件版本：</span>
                                <span class="version-value">{{ item.currentVersion }}</span>
                            </div>
                            <div class="version-item available">
                                <span class="version-label">可更新固件版本：</span>
                                <span class="version-value">{{ item.availableVersion }}</span>
                            </div>
                        </div>
                        <div class="update-description">
                            {{ item.description }}
                        </div>
                    </div>

                    <div class="update-action">
                        <CustomButton variant="primary" :loading="item.updating" @click="handleUpdate(item)">
                            {{ item.updating ? '更新中...' : '立即更新' }}
                        </CustomButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { ElMessage } from 'element-plus'
import CustomButton from '/@/components/form/CustomButton.vue'

interface UpdateItem {
    id: number
    name: string
    image: string
    currentVersion: string
    availableVersion: string
    description: string
    updating: boolean
}

// 系统更新列表数据
const updateList = ref<UpdateItem[]>([
    {
        id: 1,
        name: 'DJI FlyCart 30',
        image: '/assets/CodeBubbyAssets/83938_1185/1.png',
        currentVersion: '1110C4F-45F-454SF5',
        availableVersion: '1110C4F-45F-454SF5',
        description:
            '更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容',
        updating: false,
    },
    {
        id: 2,
        name: 'DJI FlyCart 30',
        image: '/assets/CodeBubbyAssets/83938_1185/2.png',
        currentVersion: '1110C4F-45F-454SF5',
        availableVersion: '1110C4F-45F-454SF5',
        description:
            '更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容',
        updating: false,
    },
    {
        id: 3,
        name: 'DJI FlyCart 30',
        image: '/assets/CodeBubbyAssets/83938_1185/3.png',
        currentVersion: '1110C4F-45F-454SF5',
        availableVersion: '1110C4F-45F-454SF5',
        description:
            '更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容',
        updating: false,
    },
    {
        id: 4,
        name: 'DJI FlyCart 30',
        image: '/assets/CodeBubbyAssets/83938_1185/4.png',
        currentVersion: '1110C4F-45F-454SF5',
        availableVersion: '1110C4F-45F-454SF5',
        description:
            '更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容更新内容',
        updating: false,
    },
])

// 处理更新
const handleUpdate = async (item: UpdateItem) => {
    try {
        item.updating = true
        ElMessage.info(`开始更新 ${item.name}...`)

        // 模拟更新过程
        await new Promise((resolve) => setTimeout(resolve, 3000))

        ElMessage.success(`${item.name} 更新完成！`)
        item.updating = false
    } catch (error) {
        ElMessage.error(`${item.name} 更新失败，请重试`)
        item.updating = false
    }
}
</script>

<style scoped>
.system-update-management {
    background: white;
    height: 100%;
    display: flex;
    flex-direction: column;
    padding: 28px;
}

.page-header {
    padding-bottom: 28px;
}

.page-title {
    font-size: 24px;
    font-weight: 600;
    color: #000000;
}

/* 更新容器 */
.update-container {
    flex: 1;
    overflow: auto;
    padding-top: 28px;
}

/* 更新网格 */
.update-grid {
    display: grid;
    grid-template-columns: repeat(2, auto);
    gap: 28px 28px;
    justify-content: center;
}

/* 更新卡片 */
.update-card {
    width: 100%;
    height: 220px;
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 16px;
    padding: 28px;
    display: flex;
    gap: 32px;
    transition: all 0.3s ease-in-out;
    box-sizing: border-box;
}

.update-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-color: rgba(0, 0, 0, 0.15);
}

/* 无人机图片 */
.drone-image {
    flex-shrink: 0;
    width: 240px;
    height: 157px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

.drone-image img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* 更新信息 */
.update-info {
    flex: 1;
    height: 164px;
    display: flex;
    flex-direction: column;
    position: relative;
}

/* 无人机名称 */
.drone-name {
    width: 162px;
    height: 34px;
    font-size: 24px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 500;
    color: #000000;
    margin: 0 0 10px 0;
    line-height: 34px;
}

/* 版本信息 */
.version-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-bottom: 16px;
}

.version-item {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    line-height: 20px;
}

.version-label {
    color: rgba(0, 0, 0, 0.6);
}

.version-value {
    color: #000000;
    font-weight: 400;
}

.version-item.current .version-value {
    color: rgba(0, 0, 0, 0.6);
}

.version-item.available .version-value {
    color: #2ba471;
    font-weight: 400;
}

/* 更新描述 */
.update-description {
    width: 100%;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: #000000;
    line-height: 20px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
}

/* 更新操作 */
.update-action {
    display: flex;
    align-items: center;
}

.update-btn {
    width: 120px;
    height: 48px;
    background: #00386d;
    border: none;
    border-radius: 12px;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: white;
}

.update-btn:hover {
    background: #004080;
}

.update-btn.is-loading {
    background: #00386d;
    opacity: 0.8;
}

/* 响应式设计 */
@media screen and (max-width: 1800px) {
    .update-grid {
        grid-template-columns: 1fr;
        justify-content: center;
    }

    .system-update-management {
        padding: 28px 20px;
    }
}

@media screen and (max-width: 920px) {
    .update-card {
        width: 100%;
        max-width: 876px;
        height: auto;
        min-height: 220px;
        flex-direction: column;
        gap: 20px;
        padding: 20px;
    }

    .drone-image {
        width: 200px;
        height: 130px;
        margin: 0 auto;
    }

    .update-info {
        width: 100%;
        height: auto;
        text-align: center;
    }

    .drone-name {
        width: 100%;
        text-align: center;
    }

    .update-description {
        width: 100%;
    }

    .update-action {
        position: static;
        display: flex;
        justify-content: center;
        margin-top: 16px;
    }
}

@media screen and (max-width: 480px) {
    .system-update-management {
        padding: 16px;
    }

    .update-card {
        padding: 16px;
    }

    .drone-image {
        width: 150px;
        height: 100px;
    }

    .drone-name {
        font-size: 20px;
        height: auto;
    }

    .version-item {
        font-size: 13px;
    }

    .update-description {
        font-size: 13px;
    }

    .update-btn {
        width: 100px;
        height: 40px;
        font-size: 13px;
    }
}
</style>
