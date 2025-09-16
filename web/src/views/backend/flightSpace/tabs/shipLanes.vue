<template>
    <div class="shipLanes">
        <div class="shipLanes-header">
            <div class="shipLanes-header-left">航线库</div>
            <el-tooltip class="box-item" effect="dark" content="新建航线库" placement="top">
                <el-icon size="16" style="cursor: pointer" @click="openAddShipLanePop = true"><FolderAdd /></el-icon>
            </el-tooltip>
        </div>
        <div class="shipLanes-content">
            <el-collapse v-model="activeName" accordion>
                <el-collapse-item :name="index" v-for="(item, index) in shipLanesFolderList" :key="index">
                    <template #title="{ isActive }">
                        <div :class="['title-wrapper', { 'is-active': isActive }]">
                            <div class="title-wrapper-left">
                                <el-icon size="14"><Folder /></el-icon>
                                <span>{{ item.name }}</span>
                            </div>
                            <div class="title-wrapper-right">
                                <!-- 搜索 -->
                                <el-tooltip class="box-item" effect="dark" content="搜索航线" placement="top">
                                    <el-icon
                                        :color="isSearch ? '#409EFF' : '#000000'"
                                        @click.stop="activeName !== '' ? (isSearch = !isSearch) : ''"
                                        size="14"
                                        style="cursor: pointer"
                                        ><Search
                                    /></el-icon>
                                </el-tooltip>
                                <!-- 新建 -->
                                <el-tooltip class="box-item" effect="dark" content="新建航线" placement="top">
                                    <el-icon @click.stop="handleAdd(item, index)" size="14" style="cursor: pointer"><Plus /></el-icon>
                                </el-tooltip>
                                <!-- 删除 -->
                                <el-tooltip class="box-item" effect="dark" content="删除航线库" placement="top">
                                    <el-icon @click.stop="handleDeleteFolder(item, index)" size="14" style="cursor: pointer"><Delete /></el-icon>
                                </el-tooltip>
                            </div>
                        </div>
                    </template>
                    <div class="shipLanes-content">
                        <el-input
                            v-if="isSearch && activeName == index"
                            v-model="searchKeyword"
                            placeholder="搜索航线名称"
                            :prefix-icon="Search"
                            @input="searchShipLanes"
                            clearable
                        />
                        <div
                            :class="['shipLanes-content-item', { active: activeShipLanes === `${index}-${index1}` }]"
                            @click="handleActive(item1, `${index}-${index1}`)"
                            v-for="(item1, index1) in hxList"
                            :key="index1"
                        >
                            <div class="item-header">
                                <div class="item-header-left">{{ item1.name }}</div>
                                <div class="item-header-right">
                                    <el-tooltip class="box-item" effect="dark" content="编辑航线" placement="top">
                                        <el-icon size="14" style="cursor: pointer" @click.stop="handleEdit(item1, `${index}-${index1}`, item)"
                                            ><EditPen
                                        /></el-icon>
                                    </el-tooltip>
                                    <el-dropdown>
                                        <span class="el-dropdown-link">...</span>
                                        <template #dropdown>
                                            <el-dropdown-menu>
                                                <el-dropdown-item @click="handleRename(item1)">重命名</el-dropdown-item>
                                                <el-dropdown-item @click="handleDownload(item1)">下载</el-dropdown-item>
                                                <el-dropdown-item @click="handleDelete(item1, `${index}-${index1}`)">删除</el-dropdown-item>
                                            </el-dropdown-menu>
                                        </template>
                                    </el-dropdown>
                                </div>
                            </div>
                            <div class="item-content">
                                <img class="item-box-image" src="/img/image/aerocraft.png" alt="" />
                                <div class="item-box-text">Matrice 3D</div>
                            </div>
                            <div class="item-content">
                                <div class="item-box-text">更新时间 {{ timeFormat(item1.update_time) }}</div>
                            </div>
                        </div>
                        <el-empty v-if="hxList.length === 0" description="暂无航线">
                            <el-button type="primary" @click="handleAdd(item, index)">添加航线</el-button>
                        </el-empty>
                    </div>
                </el-collapse-item>
            </el-collapse>
        </div>

        <!-- 新建航线 -->
        <AddShipLanes v-model="addShipLanesVisible" />
    </div>
</template>

<script setup lang="ts">
import { ref, inject, watch, onMounted, computed } from 'vue'
import { FolderAdd, Folder, Plus, EditPen, Search, Delete } from '@element-plus/icons-vue'
import AddShipLanes from '../components/addShipLanes.vue'
import { getHxList, editHx, deleteHx } from '/@/config/flyApi'
import { timeFormat } from '/@/utils/common'
import { useShipLanes } from '/@/stores/shipLanes'
import { storeToRefs } from 'pinia'
import { useProjectStore } from '/@/stores/project'
import { ElMessage, ElMessageBox } from 'element-plus'
import { useMedia } from '/@/stores/media'
import { baTableApi } from '/@/api/common'
const mediaStore = useMedia()
const { uploadApi } = storeToRefs(mediaStore)

const projectStore = useProjectStore()
const { currentProject, shipLanesFolderList, currentShipLanesFolder } = storeToRefs(projectStore)

watch(currentProject, () => {
    projectStore.getShipLanesFolderList()
    activeName.value = ''
    hxList.value = []
})

// 是否搜索
const isSearch = ref(false)

watch(isSearch, (newVal: any) => {
    if (!newVal) {
        searchKeyword.value = ''
    }
})

// 搜索关键字
const searchKeyword = ref('')

// 搜索航线
const searchShipLanes = () => {
    getHxListData()
}

const shipLanesStore = useShipLanes()
const { subForm, shipForm, activeShipLanes } = storeToRefs(shipLanesStore)

const addShipLanesVisible = ref(false)

// 添加航线库弹窗
const openAddShipLanePop = inject('openAddShipLanePop')

watch(openAddShipLanePop, (newVal: any) => {
    if (!newVal) {
        projectStore.getShipLanesFolderList()
        activeName.value = ''
        hxList.value = []
    }
})

// 航线弹窗
const openShipLanesOptionsPop = inject<string>('openShipLanesOptionsPop')

// 航线列表
const hxList = ref<any[]>([])
// 当前选中的项目
const activeName = ref<number | string>('')
watch(activeName, (newVal: number | string) => {
    if (newVal !== '') {
        subForm.value.project_id = currentProject.value?.id
        currentShipLanesFolder.value = shipLanesFolderList.value[newVal as number]
        getHxListData()
    } else {
        isSearch.value = false
    }
})

// 当前项目的id
// watch(
//     () => subForm.value.project_id,
//     (newVal: number | string) => {
//         if (newVal) {
//             getHxListData()
//         }
//     }
// )

const handleAdd = (item: any, index: number) => {
    activeShipLanes.value = ''
    openShipLanesOptionsPop.value = ''
    shipLanesStore.resetShipForm()
    addShipLanesVisible.value = true
    currentShipLanesFolder.value = item
    subForm.value.airline_floder_id = item.id
    subForm.value.project_id = currentProject.value?.id
    console.log(subForm.value)
}

const handleActive = (item: any, index: string) => {
    if (activeShipLanes.value === index) {
        // activeShipLanes.value = ''
        // openShipLanesOptionsPop.value = ''
        // shipLanesStore.resetShipForm()
        return
    } else {
        openShipLanesOptionsPop.value = ''
        shipLanesStore.resetShipForm()
    }
    setTimeout(() => {
        activeShipLanes.value = index
        subForm.value.id = item.id
        subForm.value.name = item.name
        const kmzJson = JSON.parse(item.kmz_json)
        shipLanesStore.showShipForm(kmzJson)
        openShipLanesOptionsPop.value = 'look'
    }, 100)
}

// 编辑航线
const handleEdit = (item: any, index: string, item1: any) => {
    // 重置
    activeShipLanes.value = ''
    openShipLanesOptionsPop.value = ''
    shipLanesStore.resetShipForm()
    // 编辑航线
    setTimeout(() => {
        activeShipLanes.value = index
        subForm.value.id = item.id
        subForm.value.name = item.name
        subForm.value.airline_floder_id = item1.id
        subForm.value.project_id = currentProject.value?.id
        const kmzJson = JSON.parse(item.kmz_json)
        shipLanesStore.showShipForm(kmzJson)
        openShipLanesOptionsPop.value = 'edit'
    }, 100)
}

// 重命名航线
const handleRename = (item: any) => {
    console.log(item)
    ElMessageBox.prompt('航线名称', '编辑航线名称', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        inputErrorMessage: '输入的航线名称不能为空',
        inputValue: item.name,
        inputValidator: (value: string) => {
            if (value) {
                return true
            } else {
                return false
            }
        },
    }).then(async ({ value }: any) => {
        item.name = value
        const res = await editHx(item)
        if (res.code == 1) {
            ElMessage.success('重命名成功')
            getHxListData()
        }
    })
}

// 下载航线
const handleDownload = (item: any) => {
    let file = uploadApi.value + item.kmz
    let a = document.createElement('a')
    a.href = file
    a.download = item.name + '.kmz'
    a.click()
}

// 删除航线
const handleDelete = (item: any, index: string) => {
    console.log(item)
    ElMessageBox.prompt('请确认要删除航线的名称', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        inputErrorMessage: '操作失败，输入的航线名称错误',
        inputValidator: (value: string) => {
            if (value === item.name) {
                return true
            } else {
                return false
            }
        },
    }).then(async () => {
        await deleteHx({ ids: [item.id] })
        ElMessage.success('删除成功')
        await getHxListData()
        if (activeShipLanes.value === index) {
            activeShipLanes.value = ''
            openShipLanesOptionsPop.value = ''
        }
    })
}

// 删除航线库
const handleDeleteFolder = (item: any, index: string) => {
    console.log(item)
    ElMessageBox.prompt('请确认要删除航线库的名称', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        inputErrorMessage: '操作失败，输入的航线库名称错误',
        inputValidator: (value: string) => {
            if (value === item.name) {
                return true
            } else {
                return false
            }
        },
    }).then(async () => {
        await new baTableApi('/admin/airline.Floder/').del([item.id])
        ElMessage.success('删除成功')
        await projectStore.getShipLanesFolderList()
        activeName.value = ''
        hxList.value = []
    })
}

// 获取航线列表
const getHxListData = async () => {
    const res = await getHxList({
        search: [
            { field: 'airline_floder_id', val: currentShipLanesFolder.value.id, operator: '=' },
            { field: 'project_id', val: currentProject.value?.id, operator: '=' },
            { field: 'name', val: searchKeyword.value, operator: 'LIKE' },
        ],
    })
    hxList.value = res.data.list
}

onMounted(() => {
    projectStore.getShipLanesFolderList()
})
</script>

<style scoped lang="scss">
.shipLanes {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;

    &-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
    }

    .title-wrapper {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-right: 10px;

        .title-wrapper-left {
            display: flex;
            align-items: center;
            gap: 4px;

            .title-wrapper-image {
                width: 16px;
                height: 16px;
            }

            span {
                font-size: 12px;
                line-height: 12px;
            }

            .left-number {
                font-size: 12px;
                width: 18px;
                height: 18px;
                background: #d5e5fa;
                border-radius: 4px;
                color: #000;
                text-align: center;
                line-height: 18px;
            }
        }

        .title-wrapper-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }

    .shipLanes-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 6px;
        overflow-y: auto;

        &::-webkit-scrollbar {
            display: none;
        }

        .shipLanes-content-item {
            display: flex;
            flex-direction: column;
            padding: 10px;
            gap: 10px;
            border-radius: 4px;
            background: #f5f5f5;
            border: 1px solid transparent;
            cursor: pointer;

            &:hover {
                background: #e5e5e5;
            }

            &.active {
                border: 1px solid #00386d;
            }

            .item-header {
                display: flex;
                justify-content: space-between;
                align-items: center;

                .item-header-left {
                    font-size: 12px;
                    font-weight: bold;
                }

                .item-header-right {
                    display: flex;
                    align-items: center;
                    gap: 10px;

                    .el-dropdown-link {
                        cursor: pointer;
                        font-weight: bold;
                    }
                }
            }

            .item-content {
                display: flex;
                align-items: center;
                gap: 4px;

                .item-box-image {
                    width: 16px;
                    height: 16px;
                }

                .item-box-text {
                    font-size: 12px;
                    color: #00000099;
                    line-height: 12px;
                }
            }
        }
    }
}
</style>
