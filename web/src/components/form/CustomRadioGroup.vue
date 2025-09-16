<template>
    <div class="custom-radio-group">
        <div
            v-for="option in options"
            :key="String(option.value)"
            class="radio-option"
            :class="{ active: modelValue === option.value }"
            @click="handleSelect(option.value)"
        >
            <div class="radio-icon">
                <div class="radio-dot" v-if="modelValue === option.value"></div>
            </div>
            <span>{{ option.label }}</span>
        </div>
    </div>
</template>

<script lang="ts" setup>
interface RadioOption {
    value: string | number | boolean
    label: string
}

interface Props {
    modelValue: any
    options: RadioOption[]
    disabled?: boolean
}

interface Emits {
    (e: 'update:modelValue', value: string | number | boolean): void
    (e: 'change', value: string | number | boolean): void
}

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
})

const emit = defineEmits<Emits>()

const handleSelect = (value: string | number | boolean) => {
    if (props.disabled) return

    emit('update:modelValue', value)
    emit('change', value)
}
</script>

<style scoped>
.custom-radio-group {
    width: 100%;
    display: flex;
    gap: 8px;
}

.radio-option {
    flex: 1;
    height: 48px;
    background: #f5f5f5;
    border: 2px solid #f5f5f5;
    border-radius: 12px;
    display: flex;
    align-items: center;
    padding: 0 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.radio-option:hover:not(.disabled) {
    background: #f0f0f0;
}

.radio-option.active {
    background: #f3f9ff;
    border: 2px solid #00386d;
}

.radio-option.disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.radio-icon {
    width: 16px;
    height: 16px;
    border: 2px solid #dcdcdc;
    border-radius: 50%;
    margin-right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.radio-option.active .radio-icon {
    border-color: #00386d;
}

.radio-dot {
    width: 8px;
    height: 8px;
    background: #00386d;
    border-radius: 50%;
}

.option-icon {
    width: 16px;
    height: 16px;
    margin-right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.option-icon img {
    width: 16px;
    height: 16px;
}

.radio-option span {
    font-size: 14px;
    font-family: 'PingFang SC', sans-serif;
    font-weight: 400;
    color: rgba(0, 0, 0, 0.9);
    flex: 1;
}

.radio-option.active span {
    color: #000;
}

/* 响应式设计 */
@media screen and (max-width: 768px) {
    .custom-radio-group {
        flex-direction: column;
    }

    .radio-option {
        width: 100%;
    }
}

/* 自定义宽度支持 */
.custom-radio-group.auto-width .radio-option {
    width: auto;
    min-width: 120px;
    flex: 1;
}

.custom-radio-group.full-width .radio-option {
    width: 100%;
    flex: 1;
}

/* 垂直布局 */
.custom-radio-group.vertical {
    flex-direction: column;
}

.custom-radio-group.vertical .radio-option {
    width: 100%;
}
</style>
