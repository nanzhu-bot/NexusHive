<template>
    <div class="ship-base-info" :style="{ width: openShipLanesOptionsPop === 'look' ? `calc(100% - ${310}px)` : `calc(100% - ${390}px)` }">
        <div class="info-item">
            <div class="info-item-title">航线长度</div>
            <div class="info-item-content">{{ subForm.mileage.toFixed(2) || 0 }}m</div>
        </div>
        <div class="info-item">
            <div class="info-item-title">预计执行时间</div>
            <div class="info-item-content">{{ getExecutionTime || 0 }}</div>
        </div>
        <div class="info-item">
            <div class="info-item-title">航点</div>
            <div class="info-item-content">{{ shipLanes.length || 0 }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { inject } from 'vue'
import { storeToRefs } from 'pinia'
import { useShipLanes } from '/@/stores/shipLanes'

const shipLanesStore = useShipLanes()
const { subForm, getExecutionTime, shipLanes } = storeToRefs(shipLanesStore)

const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')
defineOptions({
    name: 'shipBaseInfo',
})
</script>

<style scoped lang="scss">
.ship-base-info {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: rgba(0, 0, 0, 0.3);
    border-radius: 12px;
    z-index: 10;
    display: flex;
    padding: 10px 0;
    justify-content: center;
    gap: 60px;

    .info-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #fff;

        .info-item-title {
            font-size: 18px;
            font-weight: bold;
        }

        .info-item-content {
            font-size: 16px;
        }
    }
}
</style>
