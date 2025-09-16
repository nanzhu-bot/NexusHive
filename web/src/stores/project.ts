import { defineStore } from 'pinia'
import { ref } from 'vue'
import { baTableApi } from '/@/api/common'

export const useProjectStore = defineStore('project', () => {
    // 是否显示项目
    const isShowProject = ref(true)

    // 项目列表
    const projectList = ref([])

    // 当前项目
    const currentProject = ref(null)

    // 当前项目下的机场列表
    const aerodromeList = ref([])

    // 添加项目弹窗
    const addProjectPop = ref(false)

    // 弹窗类型
    const addProjectPopType = ref('add')

    // 当前项目下的航线文件夹
    const shipLanesFolderList = ref([])

    // 当前选中航线文件夹
    const currentShipLanesFolder = ref(null)

    // 获取项目列表
    const getProjectList = async () => {
        const res = await new baTableApi('/admin/Project/').index()
        projectList.value = res.data.list
    }

    // 搜索项目
    const searchProject = async (searchKeyword: string) => {
        const res = await new baTableApi('/admin/Project/').index({
            search: [{ field: 'name', val: searchKeyword, operator: 'LIKE' }],
        })
        projectList.value = res.data.list
    }

    // 获取机场列表
    const getAerodromeList = async () => {
        const res = await new baTableApi('/admin/Equipment/').index({
            search: [{ field: 'project_id', val: currentProject.value?.id, operator: '=' }],
        })
        aerodromeList.value = res.data.list
    }

    // 获取航线文件夹列表
    const getShipLanesFolderList = async () => {
        const res = await new baTableApi('/admin/airline.Floder/').index({
            search: [{ field: 'project_id', val: currentProject.value?.id, operator: '=' }],
        })
        shipLanesFolderList.value = res.data.list
    }

    // 提交项目成功后
    const submitProjectSuccess = async () => {
        getProjectList()
    }

    // 重置
    const reset = () => {
        isShowProject.value = true
        currentProject.value = null
        addProjectPop.value = false
    }
    return {
        isShowProject,
        projectList,
        currentProject,
        addProjectPop,
        aerodromeList,
        shipLanesFolderList,
        currentShipLanesFolder,
        addProjectPopType,
        // 方法
        getProjectList,
        searchProject,
        getAerodromeList,
        getShipLanesFolderList,
        submitProjectSuccess,
        reset,
    }
})
