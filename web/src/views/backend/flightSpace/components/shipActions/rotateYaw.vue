<template>
    <div class="rotate-yaw-container">
        <div class="rotate-yaw-header">
            <div class="rotate-yaw-title">飞行器偏航角</div>
            <div class="rotate-yaw-rigth">
                <span>{{ value }}</span
                >°
            </div>
        </div>
        <div class="rotate-yaw-slider">
            <div class="rotate-yaw-slider-left" @click="value--">-</div>
            <div class="rotate-yaw-slider-center">
                <el-slider v-model="value" :min="-180" :max="180" :step="1" />
            </div>
            <div class="rotate-yaw-slider-right" @click="value++">+</div>
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
        return Number(props.modelValue.aircraftHeading) || 0
    },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, aircraftHeading: value })
    },
})
</script>

<style scoped lang="scss">
.rotate-yaw-container {
    .rotate-yaw-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .rotate-yaw-title {
            font-size: 14px;
            font-weight: 600;
        }

        .rotate-yaw-rigth {
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

    .rotate-yaw-slider {
        display: flex;
        align-items: center;
        gap: 20px;

        .rotate-yaw-slider-left {
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

        .rotate-yaw-slider-center {
            flex: 1;
        }

        .rotate-yaw-slider-right {
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
