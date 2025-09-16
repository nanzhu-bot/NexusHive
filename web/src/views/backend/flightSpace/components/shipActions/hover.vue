<template>
    <div class="hover-container">
        <div class="hover-header">
            <div class="hover-title">悬停等待</div>
        </div>
        <div class="hover-slider">
            <div class="slider-left">
                <div :class="{ disabled: value < 101 }" @click="changeValue(-100)">-100</div>
                <div :class="{ disabled: value < 11 }" @click="changeValue(-10)">-10</div>
                <div :class="{ disabled: value < 2 }" @click="changeValue(-1)">-1</div>
            </div>
            <div class="slider-center">
                <text class="slider-center-text">{{ value }}</text
                >s
            </div>
            <div class="slider-right">
                <div :class="{ disabled: value > 1799 }" @click="changeValue(1)">+1</div>
                <div :class="{ disabled: value > 1790 }" @click="changeValue(10)">+10</div>
                <div :class="{ disabled: value > 1700 }" @click="changeValue(100)">+100</div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },
})
const emit = defineEmits(['update:modelValue'])
const value = computed({
    get() {
        return Number(props.modelValue.hoverTime) || 0
    },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, hoverTime: value })
    },
})
const changeValue = (changeValue: number) => {
    if (value.value + changeValue < 1) {
        return
    }
    if (value.value + changeValue > 1800) {
        return
    }
    value.value += changeValue
}
</script>

<style scoped lang="scss">
.hover-container {
    .hover-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .hover-title {
            font-size: 14px;
            font-weight: 600;
        }
    }

    .hover-slider {
        display: flex;
        align-items: center;

        .slider-left,
        .slider-right {
            display: flex;
            align-items: center;
            gap: 4px;

            div {
                background-color: #2d8cf0;
                border-radius: 4px;
                padding: 4px 8px;
                color: #fff;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                user-select: none;

                &:hover {
                    background-color: #1d7be0;
                }

                &.disabled {
                    background-color: #f0f0f0;
                    color: #999;
                    cursor: not-allowed;
                }
            }
        }

        .slider-center {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2px;

            .slider-center-text {
                font-weight: 600;
                font-size: 18px;
                color: #2d8cf0;
            }
        }
    }
}
</style>
