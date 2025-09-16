<template>
    <div class="right-media-library">
        <div class="header">
            <div
                class="header-item"
                v-for="(item, index) in aerodromeList"
                :key="index"
                :class="{ 'header-item-active': selectedShipLane === index }"
                @click="selectedShipLane = index"
            >
                <img class="header-item-img" src="/img/image/folder.png" alt="" />
                <div class="header-item-title">
                    <div class="header-item-title-name">{{ item.nickname }}</div>
                    <!-- <div class="header-item-title-num">{{ item.files.length }}个文件</div> -->
                </div>
            </div>
        </div>
        <div class="content">
            <div class="content-header" v-if="aerodromeList.length > 0">
                <div class="content-header-title">
                    <div class="breadcrumb">
                        <div class="breadcrumb-item" v-for="(item, index) in breadcrumbList" :key="index">
                            <span @click="handleBreadcrumbClick(item, index)">{{ item }}</span>
                            <span class="breadcrumb-item-separator" v-if="index !== breadcrumbList.length - 1"> / </span>
                        </div>
                    </div>
                </div>
                <div class="content-header-right">
                    <el-input v-model="form.search" clearable :prefix-icon="Search" placeholder="飞行记录搜索" style="width: 240px" />
                    <el-date-picker
                        v-model="form.time"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                    />
                    <!-- 批量下载 -->
                    <!-- <el-button type="primary" @click="handleBatchDownload">批量下载</el-button> -->
                </div>
            </div>
            <div class="content-list" v-if="!mediaFileList.length">
                <div
                    class="content-item"
                    v-for="(item, index) in flightTaskList"
                    :key="index"
                    :class="{ 'content-item-active': currentShipLane === index }"
                    @click="handleDoubleClick(item, index)"
                >
                    <img class="content-item-img" src="/img/image/folder.png" alt="" />
                    <div class="content-item-title">
                        <div class="content-item-title-name">{{ item.airline.name }}</div>
                    </div>
                </div>
            </div>

            <el-table :data="mediaFileList" style="width: 100%" v-else>
                <!-- <el-table-column type="selection" width="55" /> -->
                <el-table-column prop="name" label="文件名" align="center" />
                <el-table-column prop="object_key" label="图片" align="center">
                    <template #default="scope">
                        <el-image class="table-img" :src="baseUrl + scope.row.object_key" />
                    </template>
                </el-table-column>
                <el-table-column prop="sn" label="机舱sn" align="center" />
                <el-table-column prop="create_time" label="创建时间" align="center">
                    <template #default="scope">
                        <div>{{ timeFormat(scope.row.create_time) }}</div>
                    </template>
                </el-table-column>
                <el-table-column fixed="right" label="操作" align="center" min-width="120">
                    <template #default="scope">
                        <el-popconfirm title="是否下载图片" placement="top" @confirm="handleDownload(scope.row)">
                            <template #reference>
                                <el-button type="primary" :icon="Download" circle></el-button>
                            </template>
                        </el-popconfirm>
                        <el-popconfirm title="是否删除图片" placement="top" @confirm="handleDelete(scope.row)">
                            <template #reference>
                                <el-button type="danger" :icon="Delete" circle></el-button>
                            </template>
                        </el-popconfirm>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="footer">
            <paginationControl
                v-model:current-page="currentPage"
                v-model:page-size="pageSize"
                :total="total"
                @pagination-change="handlePaginationChange"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, watch, useTemplateRef, provide } from 'vue'
import { Search, Download, Delete } from '@element-plus/icons-vue'
import { baTableApi } from '/@/api/common'
import baTableClass from '/@/utils/baTable'
import { useI18n } from 'vue-i18n'
import Table from '/@/components/table/index1.vue'
import { timeFormat } from '/@/utils/common'
import { ElMessage } from 'element-plus'
import paginationControl from '/@/components/paginationControl/index.vue'
import { useProjectStore } from '/@/stores/project'
import { storeToRefs } from 'pinia'

const { t } = useI18n()

const baseUrl = import.meta.env.VITE_IMG_BASE_URL

const projectStore = useProjectStore()
const { currentProject, aerodromeList } = storeToRefs(projectStore)

watch(currentProject, async () => {
    await projectStore.getAerodromeList()
    if (selectedShipLane.value != 0) {
        selectedShipLane.value = 0
    } else {
        getMediaLibrary()
    }
})

// 项目接口
const api = new baTableApi('/admin/Equipment/')

// 飞行任务接口
const flightTaskApi = new baTableApi('/admin/Flighttask/')

// 媒体文件接口
const mediaFileApi = new baTableApi('/admin/media/')

// 面包屑
const breadcrumbList = ref<string[]>([])

// 项目文件夹下的飞行任务
const flightTaskList = ref([])
// 媒体文件
const mediaFileList = ref([])
// 当前页码
const currentPage = ref(1)
// 每页条数
const pageSize = ref(10)
// 总条数
const total = ref(0)

// 获取项目文件夹
const getMediaLibrary = async () => {
    if (aerodromeList.value.length === 0) return
    breadcrumbList.value = [aerodromeList.value[selectedShipLane.value].nickname]
    getFlightTask()
}

// 获取项目文件夹下的飞行任务
const getFlightTask = async () => {
    console.log(aerodromeList.value, selectedShipLane.value)

    if (aerodromeList.value.length === 0) return
    const res = await flightTaskApi.index({
        search: [
            {
                field: 'equipment_id',
                val: aerodromeList.value[selectedShipLane.value].id,
                operator: 'eq',
            },
        ],
    })
    flightTaskList.value = res.data.list
}

// 获取媒体文件
const getMediaFile = async (bid: string | number) => {
    const res = await mediaFileApi.index({
        search: getSearch(bid),
        page: currentPage.value,
        limit: pageSize.value,
    })
    if (!res.data.list.length) {
        ElMessage.warning('暂无媒体文件')
        mediaFileList.value = []
        breadcrumbList.value = breadcrumbList.value.slice(0, 1)
        return
    }
    mediaFileList.value = res.data.list
    total.value = res.data.total
    if (mediaFileList.value.length > 0 && !breadcrumbList.value.includes(flightTaskList.value[currentShipLane.value].airline.name)) {
        breadcrumbList.value.push(flightTaskList.value[currentShipLane.value].airline.name)
    }
}

// 获取筛选条件
const getSearch = (bid: string | number) => {
    const search = []
    search.push({ field: 'flight_id', val: bid, operator: '=' })
    if (form.search) {
        search.push({ field: 'name', val: form.search, operator: 'like' })
    }
    if (form.time) {
        search.push({ field: 'create_time', val: form.time, operator: 'between' })
    }
    return search
}

// 面包屑点击
const handleBreadcrumbClick = (item: string, index: number) => {
    if (index == 0) {
        mediaFileList.value = []
        breadcrumbList.value = breadcrumbList.value.slice(0, 1)
        getFlightTask()
        return
    }
}

onMounted(() => {
    getMediaLibrary()
})

// 选择的机舱文件夹
const selectedShipLane = ref(0)

watch(selectedShipLane, (newVal: number) => {
    breadcrumbList.value = [aerodromeList.value[newVal].nickname]
    getFlightTask()
})

// 当前选中的飞行任务
const currentShipLane = ref(0)

// 是否双击
const isDoubleClick = ref(false)

// 双击
const handleDoubleClick = (item: any, index: number) => {
    if (currentShipLane.value !== index) {
        currentShipLane.value = index
        isDoubleClick.value = false
    }
    if (isDoubleClick.value) {
        getMediaFile(item.bid)
        isDoubleClick.value = false
        return
    }
    isDoubleClick.value = true
    setTimeout(() => {
        isDoubleClick.value = false
    }, 1000)
}

// 搜索
const form = reactive({
    search: '',
    time: '',
})

watch(
    () => form.search,
    (newVal: string) => {
        getMediaFile(flightTaskList.value[currentShipLane.value].bid)
    }
)

watch(
    () => form.time,
    (newVal: string) => {
        getMediaFile(flightTaskList.value[currentShipLane.value].bid)
    }
)

// 下载
const handleDownload = (row: any) => {
    window.open(baseUrl + row.object_key, '_blank')
}

// 批量下载
const handleBatchDownload = () => {
    console.log(mediaFileList.value)
}

// 删除
const handleDelete = (row: any) => {
    mediaFileApi.del([row.id]).then((res) => {
        if (res.code == 1) {
            ElMessage.success('删除成功')
            getMediaFile(flightTaskList.value[currentShipLane.value].bid)
        }
    })
}

// 分页
const handlePaginationChange = (val: any) => {
    currentPage.value = val.currentPage
    pageSize.value = val.pageSize
    getMediaFile(flightTaskList.value[currentShipLane.value].bid)
}
</script>

<style scoped lang="scss">
:deep(.el-input__wrapper) {
    box-shadow: none !important;
    background: #f7f7f7;
    border-radius: 12px;
    border: 2px solid #f7f7f7;
}
:deep(.el-input__icon) {
    color: #000000;
}

.right-media-library {
    position: absolute;
    right: 10px;
    top: 10px;
    bottom: 10px;
    width: calc(100% - 310px);
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    z-index: 99;
    display: flex;
    flex-direction: column;

    .header {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 16px;
        margin-bottom: 16px;

        .header-item {
            width: 220px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid transparent;

            &:hover {
                background: #f5f5f5;
            }

            &-active {
                background: #f3f9ff;
                border-color: #00386d;
            }

            &-img {
                width: 40px;
                height: 40px;
            }

            &-title {
                display: flex;
                flex-direction: column;
                gap: 4px;

                &-name {
                    font-size: 14px;
                    font-weight: bold;
                }

                &-num {
                    font-size: 12px;
                    color: #999;
                }
            }
        }
    }

    .content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
        overflow: auto;

        .content-header {
            display: flex;
            align-items: center;
            justify-content: space-between;

            &-title {
            }

            &-right {
                display: flex;
                align-items: center;
                gap: 16px;
            }
        }

        .content-list {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .content-item {
            width: 220px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid transparent;

            &:hover {
                background: #f5f5f5;
            }

            &-active {
                background: #f3f9ff;
                border-color: #00386d;
            }

            &-img {
                width: 40px;
                height: 40px;
            }

            &-title {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
        }
    }
}

.breadcrumb {
    display: flex;
    align-items: center;
    font-size: 16px;
    gap: 8px;
    color: #000;

    .breadcrumb-item {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;

        &-separator {
            font-size: 24px;
        }
    }
}

.table-img {
    width: 60px;
    height: 60px;
    border-radius: 10px;
}
</style>
