<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('article.quick Search Fields') })"
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
import { article } from '/@/api/controllerUrls'
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
    new baTableApi(article),
    {
        pk: 'article_id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('article.article_id'), prop: 'article_id', align: 'center' },
            { label: t('article.cat_id'), prop: 'cat_id', align: 'center' },
            { label: t('article.title'), prop: 'title', align: 'center' },
            { label: t('article.author'), prop: 'author', align: 'center' },
            { label: t('article.author_email'), prop: 'author_email', align: 'center' },
            { label: t('article.keywords'), prop: 'keywords', align: 'center' },
            { label: t('article.article_type'), prop: 'article_type', align: 'center', operator: 'RANGE' },
            { label: t('article.is_open'), prop: 'is_open', align: 'center', operator: 'RANGE' },
            { label: t('article.add_time'), prop: 'add_time', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('article.file_url'), prop: 'file_url', align: 'center' },
            { label: t('article.open_type'), prop: 'open_type', align: 'center', operator: 'RANGE' },
            { label: t('article.link'), prop: 'link', align: 'center' },
            { label: t('article.description'), prop: 'description', align: 'center' },
            { label: t('article.order_num'), prop: 'order_num', align: 'center', operator: 'RANGE' },
            { label: t('operate'), align: 'center', width: 100, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'article_id', order: 'desc' },
    },
    {
        defaultItems: {"article_type":"2","is_open":"1","add_time":"0","open_type":"0","order_num":"1"},
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
        name: 'article',
    })
</script>

<style scoped lang="scss"></style>
