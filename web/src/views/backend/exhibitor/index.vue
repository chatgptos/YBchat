<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('exhibitor.quick Search Fields') })"
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
import { exhibitor } from '/@/api/controllerUrls'
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
    new baTableApi(exhibitor),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('exhibitor.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('exhibitor.exhibitor_id'), prop: 'exhibitor_id', align: 'center' },
            { label: t('exhibitor.name'), prop: 'name', align: 'center' },
            { label: t('exhibitor.company_name'), prop: 'company_name', align: 'center' },
            { label: t('exhibitor.company_name_en'), prop: 'company_name_en', align: 'center' },
            { label: t('exhibitor.contact_address'), prop: 'contact_address', align: 'center' },
            { label: t('exhibitor.contact_address_en'), prop: 'contact_address_en', align: 'center' },
            { label: t('exhibitor.linkman'), prop: 'linkman', align: 'center' },
            { label: t('exhibitor.linkman_en'), prop: 'linkman_en', align: 'center' },
            { label: t('exhibitor.contact_number'), prop: 'contact_number', align: 'center', operator: 'RANGE' },
            { label: t('exhibitor.fax'), prop: 'fax', align: 'center' },
            { label: t('exhibitor.email'), prop: 'email', align: 'center' },
            { label: t('exhibitor.siturl'), prop: 'siturl', align: 'center' },
            { label: t('exhibitor.cellphone'), prop: 'cellphone', align: 'center' },
            { label: t('exhibitor.postcode'), prop: 'postcode', align: 'center' },
            { label: t('exhibitor.number'), prop: 'number', align: 'center', operator: 'RANGE' },
            { label: t('exhibitor.phone_pre1'), prop: 'phone_pre1', align: 'center' },
            { label: t('exhibitor.phone_pre2'), prop: 'phone_pre2', align: 'center' },
            { label: t('exhibitor.fax_pre1'), prop: 'fax_pre1', align: 'center' },
            { label: t('exhibitor.fax_pre2'), prop: 'fax_pre2', align: 'center' },
            { label: t('exhibitor.boothNum'), prop: 'boothNum', align: 'center', operator: 'RANGE' },
            { label: t('exhibitor.productServices'), prop: 'productServices', align: 'center' },
            { label: t('exhibitor.product_services_en'), prop: 'product_services_en', align: 'center' },
            { label: t('exhibitor.others'), prop: 'others', align: 'center' },
            { label: t('exhibitor.others_en'), prop: 'others_en', align: 'center' },
            { label: t('exhibitor.edit_user'), prop: 'edit_user', align: 'center' },
            { label: t('exhibitor.edit_time'), prop: 'edit_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('exhibitor.company_name_en2zh'), prop: 'company_name_en2zh', align: 'center' },
            { label: t('exhibitor.approved_flag'), prop: 'approved_flag', align: 'center', operator: 'RANGE' },
            { label: t('exhibitor.approved_time'), prop: 'approved_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('exhibitor.approved_ip'), prop: 'approved_ip', align: 'center' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"approved_flag":"0","approved_time":"0"},
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
        name: 'exhibitor',
    })
</script>

<style scoped lang="scss"></style>
