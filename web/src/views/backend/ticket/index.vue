<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('ticket.quick Search Fields') })"
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
import { ticket } from '/@/api/controllerUrls'
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
    new baTableApi(ticket),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('ticket.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('ticket.user_id'), prop: 'user_id', align: 'center' },
            { label: t('ticket.topic_id'), prop: 'topic_id', align: 'center' },
            { label: t('ticket.exhibition_id'), prop: 'exhibition_id', align: 'center' },
            { label: t('ticket.ticket_qr_code_url_img'), prop: 'ticket_qr_code_url_img', align: 'center' },
            { label: t('ticket.ticket_desc'), prop: 'ticket_desc', align: 'center' },
            { label: t('ticket.type'), prop: 'type', align: 'center', operator: 'RANGE' },
            { label: t('ticket.status'), prop: 'status', align: 'center', operator: 'RANGE' },
            { label: t('ticket.apply_time'), prop: 'apply_time', align: 'center' },
            { label: t('ticket.enable_time'), prop: 'enable_time', align: 'center' },
            { label: t('ticket.views'), prop: 'views', align: 'center', operator: 'RANGE' },
            { label: t('ticket.address'), prop: 'address', align: 'center' },
            { label: t('ticket.ticket_fee'), prop: 'ticket_fee', align: 'center', operator: 'RANGE' },
            { label: t('ticket.ticket_name'), prop: 'ticket_name', align: 'center' },
            { label: t('ticket.ticket_img'), prop: 'ticket_img', align: 'center' },
            { label: t('ticket.free_money'), prop: 'free_money', align: 'center', operator: 'RANGE' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"type":"1","status":"1","views":"0","ticket_fee":"0.00","free_money":"0.00"},
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
        name: 'ticket',
    })
</script>

<style scoped lang="scss"></style>
