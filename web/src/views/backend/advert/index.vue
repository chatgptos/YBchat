<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('advert.quick Search Fields') })"
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
import { advert } from '/@/api/controllerUrls'
import { defaultOptButtons } from '/@/components/table'
import { baTableApi } from '/@/api/common'
import { useI18n } from 'vue-i18n'
import PopupForm from './popupForm.vue'
import Table from '/@/components/table/index.vue'
import TableHeader from '/@/components/table/header/index.vue'

const { t } = useI18n()
const tableRef = ref()
const optButtons = defaultOptButtons(["weigh-sort","edit","delete"])
const baTable = new baTableClass(
    new baTableApi(advert),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('advert.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('advert.admin_id'), prop: 'admin_id', align: 'center' },
            { label: t('advert.module'), prop: 'module', align: 'center', render: 'tag', replaceValue: { open: t('advert.module open'), page: t('advert.module page'), category: t('advert.module category'), first: t('advert.module first'), other: t('advert.module other') } },
            { label: t('advert.category_id'), prop: 'category_id', align: 'center' },
            { label: t('advert.type'), prop: 'type', align: 'center', render: 'tag', replaceValue: { banner: t('advert.type banner'), image: t('advert.type image'), video: t('advert.type video') } },
            { label: t('advert.media'), prop: 'media', align: 'center' },
            { label: t('advert.url'), prop: 'url', align: 'center' },
            { label: t('advert.title'), prop: 'title', align: 'center' },
            { label: t('advert.city'), prop: 'city', align: 'center' },
            { label: t('advert.startdate'), prop: 'startdate', align: 'center' },
            { label: t('advert.enddate'), prop: 'enddate', align: 'center' },
            { label: t('advert.views'), prop: 'views', align: 'center', operator: 'RANGE' },
            { label: t('advert.show'), prop: 'show', align: 'center', operator: 'RANGE' },
            { label: t('advert.created'), prop: 'created', align: 'center', operator: 'RANGE' },
            { label: t('advert.modified'), prop: 'modified', align: 'center', operator: 'RANGE' },
            { label: t('advert.deleted'), prop: 'deleted', align: 'center', operator: 'RANGE' },
            { label: t('advert.weigh'), prop: 'weigh', align: 'center', sortable: 'custom', operator: false },
            { label: t('advert.status'), prop: 'status', align: 'center', render: 'tag', replaceValue: { normal: t('advert.normal'), hidden: t('advert.hidden') } },
            { label: t('operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: {"views":"0","show":"0","weigh":"0","status":"normal"},
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
        name: 'advert',
    })
</script>

<style scoped lang="scss"></style>
