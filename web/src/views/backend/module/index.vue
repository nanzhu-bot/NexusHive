<template>
    <div class="default-main ba-table-box">
        <TableHeader />
        <Tabs />
        <GoodsInfo />
        <CommonDialog />
        <BaAccountDialog v-model="state.dialog.baAccount" :login-callback="() => (state.dialog.baAccount = false)" />
    </div>
</template>

<script setup lang="ts">
import { onActivated, onDeactivated, onMounted, onUnmounted } from 'vue'
import CommonDialog from './components/commonDialog.vue'
import GoodsInfo from './components/goodsInfo.vue'
import TableHeader from './components/tableHeader.vue'
import Tabs from './components/tabs.vue'
import { loadData } from './index'
import { state } from './store'
import BaAccountDialog from '/@/layouts/backend/components/baAccount.vue'
import { closeHotUpdate, openHotUpdate } from '/@/utils/vite'

defineOptions({
    name: 'moduleStore/moduleStore',
})

onMounted(() => {
    loadData()
    closeHotUpdate('modules')
})
onActivated(() => {
    closeHotUpdate('modules')
})
onDeactivated(() => {
    openHotUpdate('modules')
})
onUnmounted(() => {
    openHotUpdate('modules')
})
</script>

<style scoped lang="scss">
:deep(.goods-tag) .el-tag {
    margin: 0 6px 6px 0;
}
</style>
