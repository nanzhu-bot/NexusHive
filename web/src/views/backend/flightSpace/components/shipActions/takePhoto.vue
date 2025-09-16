<template>
    <div class="take-photo-container">
        <div class="take-photo-header">
            <div class="take-photo-title">DJI_YYYYMMDDhhmm_XXX_</div>
        </div>
        <div class="take-photo-content">
            <el-input v-model="fileSuffix" placeholder="生成文件名的后缀" clearable />
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
const fileSuffix = computed({
    get() {
        return props.modelValue.fileSuffix
    },
    set(value) {
        emit('update:modelValue', { ...props.modelValue, fileSuffix: value })
    },
})
</script>

<style scoped lang="scss">
.take-photo-container {
    .take-photo-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .take-photo-title {
            font-size: 14px;
            font-weight: 600;
        }
    }
}
</style>
