<template>
    <div class="left-tabs-container">
        <div class="left-tabs">
            <div class="left-tabs-header">
                <el-select v-model="value" placeholder="Select" style="width: 210px" @change="handleProjectChange">
                    <el-option v-for="item in options" :key="item.value" :label="item.label" :value="item.value" />
                </el-select>
                <div class="back-icon-box">
                    <el-tooltip class="box-item" effect="dark" content="退出飞行空间" placement="top">
                        <img src="/img/image/back.png" alt="退出" class="back-icon" @click="handleBack" />
                    </el-tooltip>
                </div>
            </div>
            <div class="left-tabs-content">
                <div
                    class="left-tabs-content-item"
                    :class="{ active: activeTab === index }"
                    v-for="(item, index) in tabsList"
                    :key="index"
                    @click="handleTabClick(item, index)"
                >
                    <el-tooltip effect="dark" :content="item.name" placement="top">
                        <img :src="item.image" :alt="item.name" class="left-tabs-content-item-image" />
                    </el-tooltip>
                </div>
            </div>
        </div>

        <div class="left-content">
            <component :is="tabsList[activeTab].component" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, inject, watch, markRaw, computed, onMounted } from 'vue'
import Aerodrome from '../tabs/aerodrome.vue'
import Logotype from '../tabs/logotype.vue'
import ShipLanes from '../tabs/shipLanes.vue'
import Assignment from '../tabs/assignment.vue'
import MediaLibrary from '../tabs/mediaLibrary.vue'
import { useProjectStore } from '/@/stores/project'
import { storeToRefs } from 'pinia'
import { useShipLanes } from '/@/stores/shipLanes'
import { useMapStore } from '/@/stores/map'

const projectStore = useProjectStore()
const { currentProject, projectList, isShowProject } = storeToRefs(projectStore)

const shipLanesStore = useShipLanes()
const isShowLeft = inject<any>('isShowLeft')
const mapStore = useMapStore()

// 定义接口
interface TabItem {
    name: string
    image: string
    component: string
}

const value = ref(currentProject.value?.id || '')
const options = computed(() => {
    return projectList.value.map((item: any) => ({
        value: item.id,
        label: item.name,
    }))
})

// 机场设置
const aerodromeSetting = inject<any>('aerodromeSetting')

// 当前选中的标签
const activeTab = inject<any>('activeTab')

// 打开航线配置弹窗
const openShipLanesOptionsPop = inject<any>('openShipLanesOptionsPop')

// 监听当前选中的标签
watch(activeTab, (newVal: number) => {
    openShipLanesOptionsPop.value = ''
    // mapStore.deleteRoute()
    if (newVal != 0) aerodromeSetting.value = false
})

// 标签列表
const tabsList = ref([
    { name: '机场', image: '/img/image/tabs1.png', component: markRaw(Aerodrome) },
    // { name: '地图标识', image: 'tabs2.png', component: markRaw(Logotype) },
    { name: '航线', image: '/img/image/tabs3.png', component: markRaw(ShipLanes) },
    { name: '任务', image: '/img/image/tabs4.png', component: markRaw(Assignment) },
    { name: '媒体库 ', image: '/img/image/tabs5.png', component: markRaw(MediaLibrary) },
])

// 标签点击
const handleTabClick = (item: TabItem, index: number) => {
    activeTab.value = index
}

const handleBack = () => {
    isShowProject.value = true
    isShowLeft.value = false
}

// 项目切换
const handleProjectChange = (val: string) => {
    currentProject.value = projectList.value.find((item: any) => item.id === val)
}
</script>

<style scoped lang="scss">
.left-tabs-container {
    position: absolute;
    left: 10px;
    top: 10px;
    bottom: 10px;
    width: 280px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 99;
}
.left-content {
    flex: 1;
    overflow-y: auto;
    // background-color: #f5f5f5 !important;

    &::-webkit-scrollbar {
        display: none;
    }
}

.left-tabs,
.left-content {
    box-shadow: 0px 0px 4px 0px #0000001a;
    display: flex;
    flex-direction: column;
    padding: 16px;
    background-color: #fff;
    border-radius: 12px;

    .left-tabs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1px solid #e5e5e5;
        gap: 10px;

        .back-icon-box {
            flex: 1;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;

            .back-icon {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
        }
    }

    .left-tabs-content {
        display: flex;
        margin-top: 16px;
        justify-content: space-between;

        .left-tabs-content-item {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;

            &.active {
                background: linear-gradient(135deg, #daf4ff 0%, #f9fdff 100%);
            }
            &-image {
                width: 20px;
                height: 20px;
            }
        }
    }
}

.fade-enter-active {
    animation: fadeIn 0.5s ease forwards;
}

.fade-leave-active {
    animation: fadeOut 0.5s ease forwards;
}
</style>
