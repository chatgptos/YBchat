<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('flBoothTopic.quick Search Fields') })"
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
import { flBoothTopic } from '/@/api/controllerUrls'
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
    new baTableApi(flBoothTopic),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('flBoothTopic.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('flBoothTopic.name'), prop: 'name', align: 'center' },
            { label: t('flBoothTopic.is_recommend'), prop: 'is_recommend', align: 'center', operator: 'RANGE' },
            { label: t('flBoothTopic.act_id'), prop: 'act_id', align: 'center' },
            { label: t('flBoothTopic.hall_id'), prop: 'hall_id', align: 'center' },
            { label: t('flBoothTopic.start_time'), prop: 'start_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('flBoothTopic.end_time'), prop: 'end_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('flBoothTopic.template'), prop: 'template', align: 'center' },
            { label: t('flBoothTopic.topic_img'), prop: 'topic_img', align: 'center' },
            { label: t('flBoothTopic.title_img'), prop: 'title_img', align: 'center' },
            { label: t('flBoothTopic.base_style'), prop: 'base_style', align: 'center' },
            { label: t('flBoothTopic.keywords'), prop: 'keywords', align: 'center' },
            { label: t('flBoothTopic.description'), prop: 'description', align: 'center' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"name":"''","is_recommend":"0","start_time":"0","end_time":"0","template":"''"},
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
        name: 'flBoothTopic',
    })
</script>

<style scoped lang="scss"></style>
