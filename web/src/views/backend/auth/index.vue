<template>
    <div class="equipment-box">
        <!-- 自定义主要标签页切换 -->
        <div class="tabs-section">
            <div class="custom-tabs">
                <div class="custom-tabs-nav">
                    <div
                        class="custom-tab-item"
                        v-for="(item, index) in tabList"
                        :key="item.name"
                        :class="{ active: tabName === index }"
                        @click="handleMainTabClick(index)"
                    >
                        {{ item.label }}
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-content">
            <component :is="tabList[tabName].component" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import group from './group/index.vue'
import admin from './admin/index.vue'
import mediaLib from './mediaLib/index.vue'

// 判断当前激活的标签页
const tabName = ref(0)
// tab列表
const tabList = [
    {
        name: 'group',
        label: '角色管理',
        component: group,
    },
    {
        name: 'admin',
        label: '用户管理',
        component: admin,
    },
    {
        name: 'mediaLib',
        label: '媒体库管理',
        component: mediaLib,
    },
]

// 处理主标签点击
const handleMainTabClick = (index: number) => {
    tabName.value = index
}
</script>

<style scoped lang="scss">
.equipment-box {
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.tabs-section {
    padding: 16px 16px;
    border-bottom: 1px solid #00000026;
}

/* 自定义标签页样式 */
.custom-tabs {
    width: 100%;
}

.custom-tabs-nav {
    display: flex;
    gap: 24px;
    height: 24px;
}

.custom-tab-item {
    font-size: 16px;
    color: #00000099;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: flex-end;
    text-decoration: none;
}

.custom-tab-item:hover {
    color: #000000;
    background: #ffffff;
}

.custom-tab-item.active {
    color: #000000;
    font-size: 24px;
    font-weight: bold;
}

.tab-content {
    width: 100%;
    flex: 1;
}

/* 设备管理子标签页样式 */
.device-sub-tabs {
    margin-bottom: 0;
}

:deep(.device-sub-tabs .el-tabs__header) {
    margin-bottom: 0;
}

:deep(.device-sub-tabs .el-tabs__item) {
    font-size: 14px;
    font-weight: 500;
    padding: 0 16px;
    height: 40px;
    line-height: 40px;
    color: #666;
}

:deep(.device-sub-tabs .el-tabs__item.is-active) {
    color: #409eff;
}

:deep(.device-sub-tabs .el-tabs__content) {
    padding: 0;
}

/* 路由链接样式重置 */
.custom-tab-item.router-link-active {
    color: #000000;
    font-size: 24px;
    font-weight: bold;
}

/* 响应式设计 */
@media screen and (max-width: 768px) {
    .tabs-section {
        padding: 0 16px;
    }

    .custom-tabs-nav {
        gap: 16px;
    }

    .custom-tab-item {
        font-size: 14px;
    }

    .custom-tab-item.active {
        font-size: 18px;
    }
}
</style>
