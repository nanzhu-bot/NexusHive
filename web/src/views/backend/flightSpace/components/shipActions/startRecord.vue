<template>
    <div class="start-record-container">
        <div class="start-record-header">
            <div class="start-record-title">DJI_YYYYMMDDhhmm_XXX_</div>
        </div>
        <div class="start-record-content">
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
.start-record-container {
    .start-record-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;

        .start-record-title {
            font-size: 14px;
            font-weight: 600;
        }
    }
}
</style>
