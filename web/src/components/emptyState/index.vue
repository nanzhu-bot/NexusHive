<template>
    <div class="empty-state">
        <div class="empty-icon">
            <el-icon :size="iconSize" :color="iconColor">
                <component :is="icon" />
            </el-icon>
        </div>
        <div class="empty-title">{{ title }}</div>
        <div class="empty-description">{{ description }}</div>
        <div v-if="showAction" class="empty-actions">
            <slot name="actions">
                <el-button v-if="actionText" :type="actionType" @click="handleAction">
                    {{ actionText }}
                </el-button>
            </slot>
        </div>
    </div>
</template>

<script lang="ts" setup>
import { Box, DocumentRemove, Search, Folder, User } from '@element-plus/icons-vue'

interface Props {
    // 图标类型
    icon?: string
    // 图标大小
    iconSize?: number
    // 图标颜色
    iconColor?: string
    // 标题
    title?: string
    // 描述
    description?: string
    // 是否显示操作区域
    showAction?: boolean
    // 操作按钮文字
    actionText?: string
    // 操作按钮类型
    actionType?: 'primary' | 'success' | 'warning' | 'danger' | 'info'
}

interface Emits {
    (e: 'action'): void
}

const props = withDefaults(defineProps<Props>(), {
    icon: 'Box',
    iconSize: 64,
    iconColor: '#d9d9d9',
    title: '暂无数据',
    description: '当前没有可显示的内容',
    showAction: false,
    actionText: '',
    actionType: 'primary',
})

const emit = defineEmits<Emits>()

// 图标映射
const iconMap = {
    Box,
    DocumentRemove,
    Search,
    Folder,
    User,
}

// 获取图标组件
const icon = computed(() => {
    return iconMap[props.icon as keyof typeof iconMap] || Box
})

// 处理操作按钮点击
const handleAction = () => {
    emit('action')
}
</script>

<script lang="ts">
import { computed } from 'vue'

export default {
    name: 'EmptyState',
}
</script>

<style scoped>
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 20px;
    text-align: center;
}

.empty-icon {
    margin-bottom: 24px;
}

.empty-title {
    font-size: 18px;
    font-weight: 500;
    color: #333;
    margin-bottom: 12px;
}

.empty-description {
    font-size: 14px;
    color: #666;
    line-height: 1.5;
    max-width: 400px;
    margin-bottom: 24px;
}

.empty-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
</style>
