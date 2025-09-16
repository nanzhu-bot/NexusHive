<template>
    <div>
        <el-tabs
            v-loading="state.loading.table"
            :element-loading-text="$t('module.Loading')"
            v-model="state.table.params.activeTab"
            type="border-card"
            class="store-tabs"
            @tab-change="onTabChange"
        >
            <el-tab-pane v-for="cat in state.table.category" :name="cat.id.toString()" :key="cat.id" :label="cat.name" class="store-tab-pane">
                <template v-if="state.table.modules[state.table.params.activeTab] && state.table.modules[state.table.params.activeTab].length > 0">
                    <el-row :gutter="15" class="goods">
                        <el-col
                            :xs="12"
                            :sm="8"
                            :md="8"
                            :lg="6"
                            :xl="4"
                            v-for="item in state.table.modules[state.table.params.activeTab]"
                            :key="item.uid"
                            class="goods-col"
                        >
                            <div @click="showInfo(item.uid)" class="goods-item suspension">
                                <el-image
                                    loading="lazy"
                                    fit="cover"
                                    class="goods-img"
                                    :src="item.logo ? item.logo : fullUrl('/static/images/local-module-logo.png')"
                                />
                                <div class="goods-footer">
                                    <div class="goods-tag" v-if="item.tags && item.tags.length > 0">
                                        <el-tag v-for="(tag, idx) in item.tags" :type="tag.type ? tag.type : 'primary'" :key="idx">
                                            {{ tag.name }}
                                        </el-tag>
                                    </div>
                                    <div class="goods-title">
                                        {{ item.title }}
                                    </div>
                                    <div class="goods-data">
                                        <span class="download-count">
                                            <Icon name="fa fa-download" color="#c0c4cc" size="13" /> {{ item.downloads ? item.downloads : '-' }}
                                        </span>
                                        <span v-if="item.state === moduleInstallState.UNINSTALLED" class="goods-price">
                                            <span class="original-price">{{ currency(item.original_price, item.currency_select) }}</span>
                                            <span class="current-price">{{ currency(item.present_price, item.currency_select) }}</span>
                                        </span>
                                        <div v-else class="goods-price">
                                            <el-tag effect="dark" :type="item.stateTag.type ? item.stateTag.type : 'primary'">
                                                {{ item.stateTag.text }}
                                            </el-tag>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </el-col>
                    </el-row>
                </template>
                <el-empty v-else class="modules-empty" :description="$t('module.No more')" />
            </el-tab-pane>
        </el-tabs>
    </div>
</template>

<script setup lang="ts">
import { currency, loadData, showInfo } from '../index'
import { state } from '../store'
import { moduleInstallState } from '../types'
import { fullUrl } from '/@/utils/common'

const onTabChange = () => {
    loadData()
}
</script>

<style scoped lang="scss">
.suspension:hover {
    z-index: 1;
}
.goods-item {
    display: block;
    margin-bottom: 15px;
    padding-bottom: 40px;
    position: relative;
    border-radius: var(--el-border-radius-base);
    background-color: var(--el-fill-color-extra-light);
    box-shadow: var(--el-box-shadow-light);
    cursor: pointer;
}
.goods-img {
    display: block;
    border-radius: var(--el-border-radius-base);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}
.modules-empty {
    width: 100%;
}
.goods-footer {
    display: block;
    overflow: hidden;
    padding: 10px 10px 0 10px;
    .goods-tag {
        min-height: 60px;
    }
    .goods-title {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-top: 6px;
        font-size: 14px;
        line-height: 18px;
    }
    .goods-data {
        display: flex;
        width: calc(100% - 20px);
        position: absolute;
        bottom: 0;
        align-items: baseline;
        padding: 10px 0;
        .download-count {
            color: var(--el-text-color-placeholder);
        }
        .goods-price {
            margin-left: auto;
        }
        .original-price {
            font-size: 13px;
            color: var(--el-text-color-placeholder);
            text-decoration: line-through;
        }
        .current-price {
            font-size: 16px;
            color: var(--el-color-danger);
            padding-left: 6px;
        }
    }
}
.el-tabs--border-card {
    border: none;
    box-shadow: var(--el-box-shadow-light);
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__header) {
    background-color: var(--ba-bg-color);
    border-bottom: none;
    border-radius: var(--el-border-radius-base);
}
.el-tabs--border-card :deep(.el-tabs__item.is-active) {
    border: 1px solid transparent;
}
.el-tabs--border-card :deep(.el-tabs__nav-wrap) {
    border-radius: var(--el-border-radius-base);
}
:deep(.store-tabs) .el-tabs__content {
    padding: 15px 15px 0 15px;
    min-height: 350px;
}
@media screen and (max-width: 520px) {
    .goods {
        .goods-col {
            max-width: 100%;
            flex-basis: 100%;
        }
    }
}
</style>
