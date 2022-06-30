<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('exhibition.quick Search Fields') })"
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
import { exhibition } from '/@/api/controllerUrls'
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
    new baTableApi(exhibition),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('exhibition.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('exhibition.exhibition_name'), prop: 'exhibition_name', align: 'center' },
            { label: t('exhibition.name'), prop: 'name', align: 'center' },
            { label: t('exhibition.business_services_img'), prop: 'business_services_img', align: 'center' },
            { label: t('exhibition.traffic_img'), prop: 'traffic_img', align: 'center' },
            { label: t('exhibition.service'), prop: 'service', align: 'center' },
            { label: t('exhibition.start_time'), prop: 'start_time', align: 'center' },
            { label: t('exhibition.end_time'), prop: 'end_time', align: 'center' },
            { label: t('exhibition.status'), prop: 'status', align: 'center', operator: 'RANGE' },
            { label: t('exhibition.remark'), prop: 'remark', align: 'center' },
            { label: t('exhibition.modified'), prop: 'modified', align: 'center', operator: 'RANGE' },
            { label: t('exhibition.created'), prop: 'created', align: 'center', operator: 'RANGE' },
            { label: t('exhibition.deleted'), prop: 'deleted', align: 'center', operator: 'RANGE' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: [],
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
        name: 'exhibition',
    })
</script>

<style scoped lang="scss"></style>
