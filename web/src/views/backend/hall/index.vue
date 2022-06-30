<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('hall.quick Search Fields') })"
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
import { hall } from '/@/api/controllerUrls'
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
    new baTableApi(hall),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('hall.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('hall.exhibition_id'), prop: 'exhibition_id', align: 'center' },
            { label: t('hall.hall_name'), prop: 'hall_name', align: 'center' },
            { label: t('hall.name'), prop: 'name', align: 'center' },
            { label: t('hall.booths_num'), prop: 'booths_num', align: 'center', operator: 'RANGE' },
            { label: t('hall.hall_map'), prop: 'hall_map', align: 'center' },
            { label: t('hall.hall_addr'), prop: 'hall_addr', align: 'center' },
            { label: t('hall.map_height'), prop: 'map_height', align: 'center', operator: 'RANGE' },
            { label: t('hall.modified'), prop: 'modified', align: 'center', operator: 'RANGE' },
            { label: t('hall.created'), prop: 'created', align: 'center', operator: 'RANGE' },
            { label: t('hall.map_width'), prop: 'map_width', align: 'center', operator: 'RANGE' },
            { label: t('hall.is_recommend'), prop: 'is_recommend', align: 'center', operator: 'RANGE' },
            { label: t('hall.hall_namein'), prop: 'hall_namein', align: 'center' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"booths_num":"0","modified":"0","created":"0","is_recommend":"0"},
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
        name: 'hall',
    })
</script>

<style scoped lang="scss"></style>
