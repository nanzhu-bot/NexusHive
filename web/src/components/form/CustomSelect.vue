<template>
    <div class="custom-select">
        <!-- 选择框 -->
        <el-select
            v-model="selectValue"
            :placeholder="placeholder"
            :disabled="disabled"
            :clearable="clearable"
            :filterable="filterable"
            :multiple="multiple"
            :collapse-tags="collapseTags"
            :collapse-tags-tooltip="collapseTagsTooltip"
            :loading="loading"
            :loading-text="loadingText"
            :no-data-text="noDataText"
            :no-match-text="noMatchText"
            class="form-select"
            @change="handleChange"
            @visible-change="handleVisibleChange"
            @remove-tag="handleRemoveTag"
            @clear="handleClear"
            @blur="handleBlur"
            @focus="handleFocus"
        >
            <el-option v-for="option in options" :key="option.value" :label="option.label" :value="option.value" :disabled="option.disabled">
                <div v-if="option.icon" class="option-content">
                    <div class="option-icon">
                        <img v-if="option.icon.startsWith('/')" :src="option.icon" :alt="option.label" />
                        <el-icon v-else><component :is="option.icon" /></el-icon>
                    </div>
                    <span>{{ option.label }}</span>
                </div>
                <span v-else>{{ option.label }}</span>
            </el-option>
        </el-select>

        <!-- 错误提示 -->
        <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

interface SelectOption {
    value: string | number | boolean
    label: string
    disabled?: boolean
    icon?: string
}

interface Props {
    modelValue: any
    label?: string
    options: SelectOption[]
    placeholder?: string
    required?: boolean
    disabled?: boolean
    clearable?: boolean
    filterable?: boolean
    multiple?: boolean
    collapseTags?: boolean
    collapseTagsTooltip?: boolean
    loading?: boolean
    loadingText?: string
    noDataText?: string
    noMatchText?: string
    errorMessage?: string
    width?: string
    height?: string
}

interface Emits {
    (e: 'update:modelValue', value: string | number | boolean | Array<string | number | boolean>): void
    (e: 'change', value: string | number | boolean | Array<string | number | boolean>): void
    (e: 'visible-change', visible: boolean): void
    (e: 'remove-tag', value: string | number | boolean): void
    (e: 'clear'): void
    (e: 'blur', event: FocusEvent): void
    (e: 'focus', event: FocusEvent): void
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '请选择',
    required: false,
    disabled: false,
    clearable: false,
    filterable: false,
    multiple: false,
    collapseTags: false,
    collapseTagsTooltip: false,
    loading: false,
    loadingText: '加载中...',
    noDataText: '无数据',
    noMatchText: '无匹配数据',
    width: '100%',
    height: '50px',
})

const emit = defineEmits<Emits>()

// 双向绑定
const selectValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
})

// 事件处理
const handleChange = (value: string | number | boolean | Array<string | number | boolean>) => {
    emit('change', value)
}

const handleVisibleChange = (visible: boolean) => {
    emit('visible-change', visible)
}

const handleRemoveTag = (value: string | number | boolean) => {
    emit('remove-tag', value)
}

const handleClear = () => {
    emit('clear')
}

const handleBlur = (event: FocusEvent) => {
    emit('blur', event)
}

const handleFocus = (event: FocusEvent) => {
    emit('focus', event)
}
</script>

<style scoped>
.custom-select {
    width: 100%;
}

/* 选择框样式 */
.form-select {
    width: v-bind(width);
}

:deep(.form-select .el-select__wrapper) {
    height: v-bind(height);
    background: white;
    /* border: 1px solid #dcdcdc; */
    /* border-radius: 12px; */
    padding: 0 12px;
    transition: all 0.3s ease;
}

:deep(.form-select .el-select__inner) {
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.9);
}

:deep(.form-select .el-select__inner::placeholder) {
    color: rgba(0, 0, 0, 0.4);
}

:deep(.form-select .el-select__wrapper:hover) {
    border-color: #00386d;
}

:deep(.form-select .el-select__wrapper.is-focus) {
    border-color: #00386d;
    /* box-shadow: 0 0 0 2px rgba(0, 56, 109, 0.1); */
}

/* 选项内容样式 */
.option-content {
    display: flex;
    align-items: center;
    gap: 8px;
}

.option-icon {
    width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.option-icon img {
    width: 16px;
    height: 16px;
}

/* 错误提示 */
.error-message {
    color: #d54941;
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    margin-top: 4px;
}

/* 禁用状态 */
:deep(.form-select.is-disabled .el-select__wrapper) {
    background-color: #f5f7fa;
    border-color: #e4e7ed;
    color: #c0c4cc;
    cursor: not-allowed;
}

:deep(.form-select.is-disabled .el-select__inner) {
    color: #c0c4cc;
    cursor: not-allowed;
}
</style>
