<template>
    <div class="add-ship">
        <div class="add-ship-header">
            <div class="add-ship-header-title">添加项目</div>
            <el-icon size="16" style="cursor: pointer" @click="closePop"><Close /></el-icon>
        </div>

        <!-- 表单内容 -->
        <el-form ref="formRef" :model="form" :rules="rules" label-width="80px" class="add-ship-form">
            <el-form-item label="项目名称" prop="name">
                <el-input v-model="form.name" placeholder="请输入项目名称" clearable />
            </el-form-item>

            <el-form-item label="项目介绍" prop="introduction">
                <el-input v-model="form.introduction" type="textarea" :rows="3" placeholder="请输入项目介绍" clearable />
            </el-form-item>

            <el-form-item label="经度" prop="longitude">
                <el-input v-model="form.longitude" placeholder="请输入经度" clearable>
                    <template #append>°</template>
                </el-input>
            </el-form-item>

            <el-form-item label="纬度" prop="latitude">
                <el-input v-model="form.latitude" placeholder="请输入纬度" clearable>
                    <template #append>°</template>
                </el-input>
                <!-- 描述 -->
                <div class="lat-desc">
                    <div class="lat-desc-item-content">请在地图上右键选择位置</div>
                </div>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="submitForm">提交</el-button>
                <el-button @click="resetForm">重置</el-button>
                <el-button @click="closePop">取消</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>

<script setup lang="ts">
import { inject, reactive, ref, watch, onMounted, onUnmounted } from 'vue'
import { Close } from '@element-plus/icons-vue'
import type { FormInstance, FormRules } from 'element-plus'
import { ElMessage } from 'element-plus'
import * as mars3d from 'mars3d'
import { useProjectStore } from '/@/stores/project'
import { storeToRefs } from 'pinia'
import { baTableApi } from '/@/api/common'

const projectStore = useProjectStore()
const { addProjectPop, addProjectPopType, currentProject } = storeToRefs(projectStore)

// 用户信息
const userInfo = JSON.parse(localStorage.getItem('adminInfo') || '{}')

// 表单引用
const formRef = ref<FormInstance>()

// 添加的表单
const form = reactive({
    // 项目名称
    name: '',
    // 项目介绍
    introduction: '',
    // 经度
    longitude: '',
    // 纬度
    latitude: '',
})

// 地图选点
const map = inject('marsMap')

onMounted(() => {
    // 添加右键菜单
    map.value.bindContextMenu(contextMenu)
    if (addProjectPopType.value === 'edit') {
        form.name = currentProject.value?.name || ''
        form.introduction = currentProject.value?.introduction || ''
        form.longitude = currentProject.value?.longitude || ''
        form.latitude = currentProject.value?.latitude || ''
    }
})

onUnmounted(() => {
    // 移除右键菜单
    map.value.unbindContextMenu(contextMenu)
})

// 创建右键菜单
const contextMenu = [
    {
        text: '地图选点',
        callback: function (event: any) {
            // 清除之前的选择点和标记
            clearSelection()
            // 添加点击事件
            const cartesian = event.cartesian
            const point = mars3d.LngLatPoint.fromCartesian(cartesian)
            form.latitude = point.lat.toFixed(6)
            form.longitude = point.lng.toFixed(6)
            addPointMarker(point)
        },
    },
]

// 添加点标记
const addPointMarker = (point: any) => {
    const graphic = new mars3d.graphic.PointEntity({
        position: [point.lng, point.lat, point.alt],
        style: {
            color: '#ff0000',
            pixelSize: 10,
            outlineColor: '#ffffff',
            outlineWidth: 2,
        },
        popup: `经度: ${point.lng.toFixed(6)}<br>纬度: ${point.lat.toFixed(6)}<br>高程: ${point.alt.toFixed(2)}`,
    })
    map.value.graphicLayer.addGraphic(graphic)
}

// 清除选择
const clearSelection = () => {
    if (map.value.graphicLayer) {
        map.value.graphicLayer.clear()
    }
}

// 表单校验规则
const rules = reactive<FormRules>({
    name: [
        { required: true, message: '请输入项目名称', trigger: 'blur' },
        { min: 2, max: 50, message: '项目名称长度在 2 到 50 个字符', trigger: 'blur' },
    ],
    longitude: [
        { required: true, message: '请输入经度', trigger: 'blur' },
        {
            pattern: /^-?((1[0-7][0-9]|[1-9]?[0-9])\.\d+|180\.0)$/,
            message: '经度范围为 -180 到 180 度',
            trigger: 'blur',
        },
    ],
    latitude: [
        { required: true, message: '请输入纬度', trigger: 'blur' },
        {
            pattern: /^-?([1-8]?[0-9]\.\d+|90\.0)$/,
            message: '纬度范围为 -90 到 90 度',
            trigger: 'blur',
        },
    ],
})

// 提交表单
const submitForm = async () => {
    if (!formRef.value) return
    if (addProjectPopType.value === 'edit') {
        form.id = currentProject.value?.id
        await new baTableApi('/admin/Project/').editPost(form)
        ElMessage.success('编辑成功')
        projectStore.submitProjectSuccess()
        closePop()
        return
    }
    try {
        await formRef.value.validate()
        // 这里可以调用API提交数据
        await new baTableApi('/admin/Project/').add(form)
        console.log('表单数据:', form)
        ElMessage.success('提交成功')
        projectStore.submitProjectSuccess()
        closePop()
    } catch (error) {
        console.error('表单验证失败:', error)
        ElMessage.error('请检查表单信息')
    }
}

// 重置表单
const resetForm = () => {
    if (!formRef.value) return
    formRef.value.resetFields()
    clearSelection()
}

// 关闭弹窗
const closePop = () => {
    clearSelection()
    addProjectPop.value = false
}
</script>

<style scoped lang="scss">
.add-ship {
    width: 440px;
    position: absolute;
    right: 10px;
    top: 10px;
    background-color: #fff;
    box-shadow: 0px 0px 4px 0px #0000001a;
    border-radius: 12px;
    padding: 16px;
    z-index: 9999;

    &-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        font-weight: bold;
        border-bottom: 1px solid #e5e5e5;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }

    &-form {
        .el-form-item {
            margin-bottom: 16px;
        }

        .el-form-item__label {
            font-weight: 500;
        }
    }
}

.fade-enter-active {
    animation: fadeIn 0.5s ease forwards;
}

.fade-leave-active {
    animation: fadeOut 0.5s ease forwards;
}

.lat-desc-item-content {
    font-size: 12px;
    color: #999;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateX(20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateX(0);
    }
    to {
        opacity: 0;
        transform: translateX(20px);
    }
}
</style>
