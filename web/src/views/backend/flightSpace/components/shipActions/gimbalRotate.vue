<template>
    <div class="gimbal-rotate-container">
        <div class="gimbal-rotate-header">
            <div class="gimbal-rotate-title">云台俯仰角</div>
            <div class="gimbal-rotate-rigth">
                <span>{{ value }}</span
                >°
            </div>
        </div>
        <div class="gimbal-rotate-slider">
            <div class="gimbal-rotate-slider-left" @click="value--">-</div>
            <div class="gimbal-rotate-slider-center">
                <el-slider v-model="value" :min="-90" :max="30" :step="1" />
            </div>
            <div class="gimbal-rotate-slider-right" @click="value++">+</div>
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
        return Number(props.modelValue.gimbalPitchRotateAngle) || 0
    },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, gimbalPitchRotateAngle: value })
    },
})
</script>

<style scoped lang="scss">
.gimbal-rotate-container {
    .gimbal-rotate-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .gimbal-rotate-title {
            font-size: 14px;
            font-weight: 600;
        }

        .gimbal-rotate-rigth {
            display: flex;
            align-items: self-start;
            gap: 6px;
            font-size: 16px;

            span {
                font-size: 18px;
                font-weight: 600;
                color: #2d8cf0;
            }
        }
    }

    .gimbal-rotate-slider {
        display: flex;
        align-items: center;
        gap: 20px;

        .gimbal-rotate-slider-left {
            width: 30px;
            height: 30px;
            background-color: #2d8cf0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }

        .gimbal-rotate-slider-center {
            flex: 1;
        }

        .gimbal-rotate-slider-right {
            width: 30px;
            height: 30px;
            background-color: #2d8cf0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
    }
}
</style>
