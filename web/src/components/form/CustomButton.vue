<template>
    <el-button
        :type="buttonType"
        :size="size"
        :disabled="disabled"
        :loading="loading"
        :icon="icon"
        :circle="circle"
        :round="round"
        :plain="plain"
        :text="text"
        :bg="bg"
        :link="link"
        :color="color"
        :dark="dark"
        :autoInsertSpace="autoInsertSpace"
        class="custom-button"
        :class="[`custom-button--${variant}`, `custom-button--${size}`, { 'custom-button--block': block }]"
        @click="handleClick"
    >
        <slot />
    </el-button>
</template>

<script lang="ts" setup>
interface Props {
    variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger' | 'info' | 'text'
    size?: 'large' | 'default' | 'small'
    disabled?: boolean
    loading?: boolean
    icon?: string
    circle?: boolean
    round?: boolean
    plain?: boolean
    text?: boolean
    bg?: boolean
    link?: boolean
    color?: string
    dark?: boolean
    autoInsertSpace?: boolean
    block?: boolean
    width?: string
    height?: string
}

interface Emits {
    (e: 'click', event: MouseEvent): void
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    size: 'default',
    disabled: false,
    loading: false,
    circle: false,
    round: false,
    plain: false,
    text: false,
    bg: false,
    link: false,
    dark: false,
    autoInsertSpace: true,
    block: false,
    width: 'auto',
    height: '48px',
})

const emit = defineEmits<Emits>()

// 映射variant到Element Plus的type
const buttonType = computed(() => {
    const typeMap = {
        primary: 'primary',
        secondary: 'default',
        success: 'success',
        warning: 'warning',
        danger: 'danger',
        info: 'info',
        text: 'primary',
    }
    return typeMap[props.variant] || 'primary'
})

const handleClick = (event: MouseEvent) => {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}
</script>

<script lang="ts">
import { computed } from 'vue'
</script>

<style scoped>
.custom-button {
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    border-radius: 12px;
    transition: all 0.3s ease;
    width: v-bind(width);
    height: v-bind(height);
}

/* 主要按钮 */
.custom-button--primary {
    background: #00386d;
    border-color: #00386d;
    color: white;
}

.custom-button--primary:hover {
    background: #004080;
    border-color: #004080;
}

.custom-button--primary:active {
    background: #002d5a;
    border-color: #002d5a;
}

/* 次要按钮 */
.custom-button--secondary {
    background: rgba(0, 0, 0, 0.1);
    border-color: rgba(0, 0, 0, 0.1);
    color: #000;
}

.custom-button--secondary:hover {
    background: rgba(0, 0, 0, 0.15);
    border-color: rgba(0, 0, 0, 0.15);
}

/* 成功按钮 */
.custom-button--success {
    background: #67c23a;
    border-color: #67c23a;
    color: white;
}

.custom-button--success:hover {
    background: #85ce61;
    border-color: #85ce61;
}

/* 警告按钮 */
.custom-button--warning {
    background: #e6a23c;
    border-color: #e6a23c;
    color: white;
}

.custom-button--warning:hover {
    background: #ebb563;
    border-color: #ebb563;
}

/* 危险按钮 */
.custom-button--danger {
    background: #f56c6c;
    border-color: #f56c6c;
    color: white;
}

.custom-button--danger:hover {
    background: #f78989;
    border-color: #f78989;
}

/* 信息按钮 */
.custom-button--info {
    background: #909399;
    border-color: #909399;
    color: white;
}

.custom-button--info:hover {
    background: #a6a9ad;
    border-color: #a6a9ad;
}

/* 文本按钮 */
.custom-button--text {
    background: transparent;
    border-color: transparent;
    color: #00386d;
}

.custom-button--text:hover {
    background: rgba(0, 56, 109, 0.1);
    color: #004080;
}

/* 尺寸变体 */
.custom-button--large {
    height: 56px;
    padding: 0 24px;
    font-size: 16px;
}

.custom-button--default {
    height: 48px;
    padding: 0 20px;
    font-size: 14px;
}

.custom-button--small {
    height: 40px;
    padding: 0 16px;
    font-size: 12px;
}

/* 块级按钮 */
.custom-button--block {
    width: 100%;
    display: block;
}

/* 禁用状态 */
.custom-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* 加载状态 */
.custom-button.is-loading {
    opacity: 0.8;
    cursor: not-allowed;
}

/* 圆形按钮 */
:deep(.custom-button.is-circle) {
    border-radius: 50%;
    padding: 12px;
}

/* 圆角按钮 */
:deep(.custom-button.is-round) {
    border-radius: 24px;
}
</style>
