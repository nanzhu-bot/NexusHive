<template>
    <div>
        <Icon
            color="var(--el-text-color-primary)"
            :name="cellValue"
            v-bind="invokeTableContextDataFun(field.customRenderAttr?.icon, { row, field, cellValue, column, index })"
        />
    </div>
</template>

<script setup lang="ts">
import { TableColumnCtx } from 'element-plus'
import { getCellValue, invokeTableContextDataFun } from '/@/components/table/index'

interface Props {
    row: TableRow
    field: TableColumn
    column: TableColumnCtx<TableRow>
    index: number
}

const props = defineProps<Props>()

const cellValue = getCellValue(props.row, props.field, props.column, props.index)
</script>
