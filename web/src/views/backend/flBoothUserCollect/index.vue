<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('flBoothUserCollect.quick Search Fields') })"
            @action="baTable.onTableHeaderAction"
        />

        <!-- 表格 -->
        <!-- 要使用`el-table`组件原有的属性，直接加在Table标签上即可 -->
        <Table ref="tableRef" @action="baTable.onTableAction" />

        <!-- 表单 -->
        <PopupForm />
    </div>
</template>

<script setup lang="ts">
import { ref, provide, onMounted } from 'vue'
import baTableClass from '/@/utils/baTable'
import { flBoothUserCollect } from '/@/api/controllerUrls'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(["edit","delete"])
const baTable = new baTableClass(
    new baTableApi(flBoothUserCollect),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('flBoothUserCollect.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('flBoothUserCollect.user_id'), prop: 'user_id', align: 'center' },
            { label: t('flBoothUserCollect.exhibitor_id'), prop: 'exhibitor_id', align: 'center' },
            { label: t('flBoothUserCollect.activity_id'), prop: 'activity_id', align: 'center' },
            { label: t('flBoothUserCollect.goods_id'), prop: 'goods_id', align: 'center' },
            { label: t('flBoothUserCollect.add_time'), prop: 'add_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('flBoothUserCollect.topic_id'), prop: 'topic_id', align: 'center' },
            { label: t('flBoothUserCollect.is_attention'), prop: 'is_attention', align: 'center', operator: 'RANGE' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"add_time":"0","is_attention":"0"},
    }
)

provide('baTable', baTable)

onMounted(() => {
    baTable.table.ref = tableRef.value
    baTable.mount()
    baTable.getIndex()?.then(() => {
        baTable.initSort()
        baTable.dragSort()
    })
})
</script>

<script lang="ts">
    import { defineComponent } from 'vue'
    export default defineComponent({
        name: 'flBoothUserCollect',
    })
</script>

<style scoped lang="scss"></style>
