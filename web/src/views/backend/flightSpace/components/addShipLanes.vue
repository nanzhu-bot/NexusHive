<template>
    <el-dialog
        v-model="centerDialogVisible"
        :close-on-click-modal="false"
        :show-close="false"
        style="border-radius: 24px; padding: 32px"
        width="750"
        align-center
    >
        <template #header="{ close, titleId, titleClass }">
            <div class="header">
                <span>创建新航线</span>
                <el-icon size="24" style="cursor: pointer" @click="handleClose"><Close /></el-icon>
            </div>
        </template>
        <div class="content">
            <div class="content-item">
                <div class="content-item-title">巡逻巡检航线</div>
                <el-radio-group v-model="subForm.type" style="width: 100%">
                    <div class="content-item-content">
                        <div class="content-item-content-box" :class="{ active: subForm.type === '0' }">
                            <el-radio id="radio" value="0" size="large" style="width: 100%; height: 100%">航点航线</el-radio>
                        </div>
                    </div>
                </el-radio-group>
            </div>
            <div class="content-item">
                <div class="content-item-title">选择飞行器</div>
                <el-radio-group v-model="form.flightType" style="width: 100%">
                    <div class="content-item-content">
                        <div class="content-item-content-box" :class="{ active: form.flightType === '1' }">
                            <el-radio id="radio" value="1" size="large" style="width: 100%; height: 100%">Matrrice 3D系列</el-radio>
                        </div>
                        <div class="content-item-content-box" :class="{ active: form.flightType === '2' }">
                            <el-radio id="radio" value="2" size="large" style="width: 100%; height: 100%">Matrrice 4系列</el-radio>
                        </div>
                    </div>
                </el-radio-group>
            </div>
            <div class="content-item">
                <div class="content-item-title">选择型号</div>
                <el-radio-group v-model="form.flightModel" style="width: 100%">
                    <div class="content-item-content">
                        <div class="content-item-content-box" :class="{ active: form.flightModel === '1' }">
                            <el-radio id="radio" value="1" size="large" style="width: 100%; height: 100%">大疆M300</el-radio>
                        </div>
                        <div class="content-item-content-box" :class="{ active: form.flightModel === '2' }">
                            <el-radio id="radio" value="2" size="large" style="width: 100%; height: 100%">经纬M30T</el-radio>
                        </div>
                    </div>
                </el-radio-group>
            </div>
            <div class="content-item">
                <div class="content-item-title">航线名称</div>
                <el-input
                    type="text"
                    placeholder="请输入航线名称"
                    clearable
                    v-model="subForm.name"
                    style="width: 100%; height: 50px; border: 2px solid #f5f5f5; border-radius: 8px"
                />
            </div>
        </div>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="handleClose" class="cancel-btn dialog-footer-btn">取消</el-button>
                <el-button @click="handleConfirm" class="confirm-btn dialog-footer-btn">提交保存</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { Close } from '@element-plus/icons-vue'
import { defineModel, inject, ref, Ref, watch } from 'vue'
import { useShipLanes } from '/@/stores/shipLanes'
import { storeToRefs } from 'pinia'
import { getSbList } from '/@/config/flyApi'

const centerDialogVisible = defineModel<boolean>('modelValue', { required: true })

watch(centerDialogVisible, (newVal) => {
    if (newVal) {
        // getSbList().then((res) => {
        //     console.log(res)
        // })
    }
})

const shipLanesStore = useShipLanes()

const { subForm } = storeToRefs(shipLanesStore)

const form = ref({
    flightType: '1',
    flightModel: '1',
})

const openShipLanesOptionsPop = inject('openShipLanesOptionsPop') as Ref<string>

const handleClose = () => {
    centerDialogVisible.value = false
}

const handleConfirm = () => {
    openShipLanesOptionsPop.value = 'add'
}
</script>

<style scoped lang="scss">
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    span {
        font-size: 24px;
        font-weight: bold;
    }
}

.content {
    display: flex;
    flex-direction: column;
    gap: 24px;

    .content-item {
        display: flex;
        flex-direction: column;
        gap: 8px;

        .content-item-title {
            font-size: 12px;
        }

        .content-item-content {
            width: 100%;
            display: flex;
            gap: 8px;

            .content-item-content-box {
                flex: 0 0 calc(50% - 4px);
                height: 50px;
                border: 2px solid #f5f5f5;
                border-radius: 8px;
                cursor: pointer;
                padding: 0 13px;
                background: #f5f5f5;

                :deep(.el-radio__input.is-checked .el-radio__inner) {
                    background: #00386d;
                    border-color: #00386d;
                }

                :deep(.el-radio__input.is-checked + .el-radio__label) {
                    color: #00386d;
                }

                &:hover {
                    background: #e5e5e5;
                }

                &.active {
                    background: #f3f9ff;
                    border-color: #00386d;
                }
            }
        }

        :deep(.el-input__wrapper.is-focus) {
            box-shadow: 0 0 0 2px #00386d;
        }
    }
}

.dialog-footer {
    .dialog-footer-btn {
        width: 120px;
        height: 48px;
        border-radius: 12px;
        font-size: 14px;

        &.cancel-btn {
            color: #000000;
            background: #0000001a;
        }

        &.confirm-btn {
            color: #fff;
            background: #00386d;
        }
    }
}
</style>
