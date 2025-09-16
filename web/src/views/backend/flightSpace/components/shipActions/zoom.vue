<template>
    <div class="zoom-container">
        <div class="zoom-header">
            <div class="zoom-title">相机变焦倍率</div>
            <div class="zoom-rigth">
                <span>{{ focalLength }}</span
                >X
            </div>
        </div>
        <div class="zoom-slider">
            <div class="zoom-slider-left" @click="focalLength--">-</div>
            <div class="zoom-slider-center">
                <el-slider v-model="focalLength" :min="1" :max="56" :step="1" />
            </div>
            <div class="zoom-slider-right" @click="focalLength++">+</div>
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
const focalLength = computed({
    get() {
        return Number(props.modelValue.focalLength) || 0
    },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, focalLength: value })
    },
})
</script>

<style scoped lang="scss">
.zoom-container {
    .zoom-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .zoom-title {
            font-size: 14px;
            font-weight: 600;
        }

        .zoom-rigth {
            display: flex;
            align-items: center;
            gap: 6px;

            span {
                font-size: 18px;
                font-weight: 600;
                color: #2d8cf0;
            }
        }
    }

    .zoom-slider {
        display: flex;
        align-items: center;
        gap: 20px;

        .zoom-slider-left {
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

        .zoom-slider-center {
            flex: 1;
        }

        .zoom-slider-right {
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
