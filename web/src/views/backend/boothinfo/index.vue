<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('boothinfo.quick Search Fields') })"
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
import { boothinfo } from '/@/api/controllerUrls'
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
    new baTableApi(boothinfo),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('boothinfo.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('boothinfo.exhibition_id'), prop: 'exhibition_id', align: 'center' },
            { label: t('boothinfo.hall_id'), prop: 'hall_id', align: 'center' },
            { label: t('boothinfo.is_recommend'), prop: 'is_recommend', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.coordinate_x'), prop: 'coordinate_x', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.coordinate_y'), prop: 'coordinate_y', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_widht'), prop: 'booth_widht', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_height'), prop: 'booth_height', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_area'), prop: 'booth_area', align: 'center' },
            { label: t('boothinfo.boothtem_id'), prop: 'boothtem_id', align: 'center' },
            { label: t('boothinfo.boothtype_id'), prop: 'boothtype_id', align: 'center' },
            { label: t('boothinfo.booth_standard'), prop: 'booth_standard', align: 'center' },
            { label: t('boothinfo.booth_type'), prop: 'booth_type', align: 'center' },
            { label: t('boothinfo.booth_num'), prop: 'booth_num', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_name'), prop: 'booth_name', align: 'center' },
            { label: t('boothinfo.sales_status'), prop: 'sales_status', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_tips'), prop: 'booth_tips', align: 'center' },
            { label: t('boothinfo.company_name'), prop: 'company_name', align: 'center' },
            { label: t('boothinfo.category'), prop: 'category', align: 'center' },
            { label: t('boothinfo.country'), prop: 'country', align: 'center' },
            { label: t('boothinfo.state'), prop: 'state', align: 'center' },
            { label: t('boothinfo.addr'), prop: 'addr', align: 'center' },
            { label: t('boothinfo.moble_phone'), prop: 'moble_phone', align: 'center' },
            { label: t('boothinfo.phone'), prop: 'phone', align: 'center' },
            { label: t('boothinfo.email'), prop: 'email', align: 'center' },
            { label: t('boothinfo.webaddr'), prop: 'webaddr', align: 'center' },
            { label: t('boothinfo.contacts'), prop: 'contacts', align: 'center' },
            { label: t('boothinfo.fax'), prop: 'fax', align: 'center' },
            { label: t('boothinfo.position'), prop: 'position', align: 'center' },
            { label: t('boothinfo.open_angle'), prop: 'open_angle', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.booth_discount'), prop: 'booth_discount', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.sales_distribution'), prop: 'sales_distribution', align: 'center' },
            { label: t('boothinfo.booth_price'), prop: 'booth_price', align: 'center' },
            { label: t('boothinfo.angle_add'), prop: 'angle_add', align: 'center' },
            { label: t('boothinfo.booth_amount'), prop: 'booth_amount', align: 'center' },
            { label: t('boothinfo.build_state'), prop: 'build_state', align: 'center' },
            { label: t('boothinfo.china_abbreviate'), prop: 'china_abbreviate', align: 'center' },
            { label: t('boothinfo.english_abbreviate'), prop: 'english_abbreviate', align: 'center' },
            { label: t('boothinfo.xiongk_num_free'), prop: 'xiongk_num_free', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.xiongk_num_change'), prop: 'xiongk_num_change', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.is_assigned'), prop: 'is_assigned', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.imp_buyernum'), prop: 'imp_buyernum', align: 'center', operator: 'RANGE' },
            { label: t('boothinfo.upload_batch'), prop: 'upload_batch', align: 'center', operator: 'RANGE' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'id', order: 'desc' },
    },
    {
        defaultItems: {"is_recommend":"0","booth_type":".","sales_status":"0","category":".","country":".","state":".","addr":".","moble_phone":".","phone":".","email":".","webaddr":".","contacts":".","fax":".","position":".","open_angle":"0","booth_discount":"0","sales_distribution":".","booth_price":".","angle_add":".","booth_amount":".","build_state":".","china_abbreviate":".","english_abbreviate":".","is_assigned":"0","imp_buyernum":"0"},
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
        name: 'boothinfo',
    })
</script>

<style scoped lang="scss"></style>
