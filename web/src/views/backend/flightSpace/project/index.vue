<template>
    <div class="project">
        <div class="project-header">
            <div class="project-header-left">项目</div>
            <div class="project-header-right">
                <el-tooltip class="box-item" effect="dark" content="搜索" placement="top">
                    <el-icon size="14" :color="isSearch ? '#409EFF' : '#000000'" @click="isSearch = !isSearch"><Search /></el-icon>
                </el-tooltip>
                <!-- 添加项目 -->
                <el-tooltip class="box-item" effect="dark" content="添加项目" placement="top">
                    <el-icon size="16" @click="addProject"><Plus /></el-icon>
                </el-tooltip>
            </div>
        </div>
        <!-- 搜索 -->
        <div class="project-search" v-if="isSearch">
            <el-input v-model="searchKeyword" placeholder="搜索项目名称" :prefix-icon="Search" @input="searchProject" clearable />
        </div>
        <div v-for="(item, index) in projectList" :key="index" class="project-item">
            <div class="item-left" @click.stop="goMap(item)">
                <div class="item-left-name">
                    <div>{{ item.name }}</div>
                    <!-- 编辑和删除 -->
                    <el-dropdown>
                        <span class="el-dropdown-link">...</span>
                        <template #dropdown>
                            <el-dropdown-menu>
                                <el-dropdown-item @click="editProject(item)">编辑</el-dropdown-item>
                                <el-dropdown-item @click="deleteProject(item)">删除</el-dropdown-item>
                            </el-dropdown-menu>
                        </template>
                    </el-dropdown>
                </div>
                <!-- 介绍 -->
                <div class="item-left-intro">{{ item.introduction || '暂无介绍' }}</div>
                <!-- 创建时间 -->
                <div class="item-left-time">创建时间：{{ timeFormat(item.create_time, 'yyyy-mm-dd') }}</div>
            </div>
            <div class="item-right">
                <el-tooltip class="box-item" effect="dark" content="进入项目" placement="top">
                    <img src="/src/assets/jr.svg" alt="进入" class="item-right-img" @click.stop="goProject(item)" />
                </el-tooltip>
            </div>
        </div>
        <el-empty v-if="projectList.length === 0" description="暂无项目">
            <el-button type="primary" @click="addProject">添加项目</el-button>
        </el-empty>
    </div>
</template>
<script setup lang="ts">
import { inject, onMounted, provide, ref, watch, computed } from 'vue'
import { useMqttStore } from '/@/stores/mqtt'
import { baTableApi } from '/@/api/common'
import { Edit, Plus, Search } from '@element-plus/icons-vue'
import { disposition } from '/@/config/disposition'
import { useI18n } from 'vue-i18n'
import { storeToRefs } from 'pinia'
import { useProjectStore } from '/@/stores/project'
import { timeFormat } from '/@/utils/common'
import { ElMessage, ElMessageBox } from 'element-plus'

const { t } = useI18n()
const map = inject('marsMap')
const isShowLeft = inject('isShowLeft')

const projectStore = useProjectStore()
const { projectList, addProjectPop, currentProject, isShowProject, addProjectPopType } = storeToRefs(projectStore)

// 是否搜索
const isSearch = ref(false)

watch(isSearch, (newVal) => {
    if (!newVal) {
        searchKeyword.value = ''
    }
})

// 搜索关键字
const searchKeyword = ref('')

// 搜索项目
const searchProject = () => {
    projectStore.searchProject(searchKeyword.value)
}

// 进入项目
const goProject = (item: any) => {
    currentProject.value = item
    isShowProject.value = false
    isShowLeft.value = true
    map.value.flyToPoint([item.longitude, item.latitude, 500])
}

// 添加项目
const addProject = () => {
    addProjectPop.value = true
    addProjectPopType.value = 'add'
}

// 编辑项目
const editProject = (item: any) => {
    addProjectPop.value = true
    addProjectPopType.value = 'edit'
    currentProject.value = item
}
// 删除项目
const deleteProject = async (item: any) => {
    ElMessageBox.prompt('请确认要删除项目的名称', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
        inputErrorMessage: '操作失败，输入的项目名称错误',
        inputValidator: (value: string) => {
            if (value === item.name) {
                return true
            } else {
                return false
            }
        },
    }).then(async () => {
        await new baTableApi('/admin/Project/').del([item.id])
        ElMessage.success('删除成功')
        await projectStore.getProjectList()
    })
}

// 进入地图
const goMap = (item: any) => {
    map.value.flyToPoint([item.longitude, item.latitude, 500])
}
</script>
<style scoped lang="scss">
.project {
    position: absolute;
    left: 10px;
    top: 10px;
    bottom: 10px;
    width: 280px;
    display: flex;
    flex-direction: column;
    z-index: 99;
    box-shadow: 0px 0px 4px 0px #0000001a;
    padding: 16px;
    background-color: #fff;
    border-radius: 12px;
    overflow: auto;
    &::-webkit-scrollbar {
        display: none;
    }

    .project-header {
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        .project-header-left {
            font-size: 14px;
        }
        .project-header-right {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }

    .project-search {
        margin: 10px 0;
    }

    .project-item {
        display: flex;
        border-bottom: 1px solid #e5e5e5;

        .item-left {
            padding: 10px 0;
            cursor: pointer;

            display: flex;
            flex-direction: column;
            gap: 4px;
            flex: 1;

            .item-left-name {
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-size: 16px;
                font-weight: bold;

                .el-dropdown-link {
                    cursor: pointer;
                    font-size: 12px;
                    line-height: 12px;
                }
            }
            .item-left-intro {
                font-size: 14px;
                margin-bottom: 10px;
                color: #00000099;
            }
            .item-left-time {
                font-size: 12px;
                color: #999;
            }
        }

        .item-right {
            height: 100%;
            padding: 0 8px;
            display: flex;
            align-items: center;
            .item-right-img {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
        }
    }
}
</style>
