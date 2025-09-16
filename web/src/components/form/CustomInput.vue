<template>
    <div class="custom-input">
        <!-- 输入框 -->
        <el-input
            v-model="inputValue"
            :type="type"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :minlength="minlength"
            :maxlength="maxlength"
            :show-word-limit="showWordLimit"
            :rows="rows"
            :autosize="autosize"
            :clearable="clearable"
            :show-password="showPassword"
            :prefix-icon="prefixIcon"
            :suffix-icon="suffixIcon"
            class="form-input"
            @input="handleInput"
            @change="handleChange"
            @blur="handleBlur"
            @focus="handleFocus"
        >
            <template #append v-if="append">
                <slot name="append"></slot>
            </template>
        </el-input>

        <!-- 错误提示 -->
        <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script lang="ts" setup>
import { computed } from 'vue'

interface Props {
    modelValue: any
    label?: string
    type?: 'text' | 'textarea' | 'password' | 'number' | 'email' | 'tel' | 'url'
    placeholder?: string
    required?: boolean
    disabled?: boolean
    readonly?: boolean
    maxlength?: number
    minlength?: number
    showWordLimit?: boolean
    rows?: number
    autosize?: boolean | { minRows?: number; maxRows?: number }
    clearable?: boolean
    showPassword?: boolean
    prefixIcon?: string
    suffixIcon?: string
    errorMessage?: string
    width?: string
    height?: string
    append?: boolean
}

interface Emits {
    (e: 'update:modelValue', value: string | number): void
    (e: 'input', value: string | number): void
    (e: 'change', value: string | number): void
    (e: 'blur', event: FocusEvent): void
    (e: 'focus', event: FocusEvent): void
}

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    placeholder: '',
    required: false,
    disabled: false,
    readonly: false,
    showWordLimit: false,
    rows: 3,
    autosize: false,
    clearable: false,
    showPassword: false,
    width: '100%',
    height: '50px',
    append: false,
})

const emit = defineEmits<Emits>()

// 双向绑定
const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
})

// 事件处理
const handleInput = (value: string | number) => {
    emit('input', value)
}

const handleChange = (value: string | number) => {
    emit('change', value)
}

const handleBlur = (event: FocusEvent) => {
    emit('blur', event)
}

const handleFocus = (event: FocusEvent) => {
    emit('focus', event)
}
</script>

<style scoped>
.custom-input {
    width: 100%;
    background: white;
    /* border: 1px solid #dcdcdc; */
    border-radius: 12px;
    transition: all 0.3s ease;
}

/* 输入框样式 */
.form-input {
    width: v-bind(width);
}

:deep(.form-input .el-input__wrapper) {
    height: v-bind(height);
    padding: 0 12px;
    /* box-shadow: none; */
    /* border-radius: 12px; */
}

:deep(.form-input .el-input__inner) {
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.9);
}

:deep(.form-input .el-input__inner::placeholder) {
    color: rgba(0, 0, 0, 0.4);
}

:deep(.form-input .el-input__wrapper:hover) {
    border-color: #00386d;
}

:deep(.form-input .el-input__wrapper.is-focus) {
    border-color: #00386d;
    /* box-shadow: 0 0 0 2px rgba(0, 56, 109, 0.1); */
}

/* 文本域样式 */
:deep(.form-input .el-textarea__inner) {
    background: white;
    border: 1px solid #dcdcdc;
    border-radius: 12px;
    padding: 12px;
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.9);
    resize: none;
    transition: all 0.3s ease;
}

:deep(.form-input .el-textarea__inner::placeholder) {
    color: rgba(0, 0, 0, 0.4);
}

:deep(.form-input .el-textarea__inner:hover) {
    border-color: #00386d;
}

:deep(.form-input .el-textarea__inner:focus) {
    border-color: #00386d;
    box-shadow: 0 0 0 2px rgba(0, 56, 109, 0.1);
}

/* 字数统计样式 */
:deep(.el-input__count) {
    background: transparent;
    color: rgba(0, 0, 0, 0.4);
    font-size: 12px;
}

/* 错误提示 */
.error-message {
    color: #d54941;
    font-size: 12px;
    font-family: 'PingFang SC', sans-serif;
    margin-top: 4px;
}

/* 禁用状态 */
:deep(.form-input.is-disabled .el-input__wrapper) {
    background-color: #f5f7fa;
    border-color: #e4e7ed;
    color: #c0c4cc;
    cursor: not-allowed;
}

:deep(.form-input.is-disabled .el-input__inner) {
    color: #c0c4cc;
    cursor: not-allowed;
}
</style>
