<template>
    <div class="default-main ba-table-box">
        <el-alert class="ba-table-alert" v-if="baTable.table.remark" :title="baTable.table.remark" type="info" show-icon />

        <!-- 表格顶部菜单 -->
        <TableHeader
            :buttons="['refresh', 'add', 'edit', 'delete', 'comSearch']"
            :quick-search-placeholder="t('quick Search Placeholder', { fields: t('testBuild.quick Search Fields') })"
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
import { testBuild } from '/@/api/controllerUrls'
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
    new baTableApi(testBuild),
    {
        pk: 'id',
        column: [
            { type: 'selection', align: 'center', operator: false },
            { label: t('testBuild.id'), prop: 'id', align: 'center', width: 70, sortable: 'custom', operator: 'RANGE' },
            { label: t('testBuild.title'), prop: 'title', align: 'center' },
            { label: t('testBuild.views'), prop: 'views', align: 'center', operator: 'RANGE' },
            { label: t('testBuild.likes'), prop: 'likes', align: 'center', operator: 'RANGE' },
            { label: t('testBuild.dislikes'), prop: 'dislikes', align: 'center', operator: 'RANGE' },
            { label: t('testBuild.status'), prop: 'status', align: 'center', render: 'tag', replaceValue: { 0: t('testBuild.status 0'), 1: t('testBuild.status 1') } },
            { label: t('testBuild.weigh'), prop: 'weigh', align: 'center', sortable: 'custom', operator: false },
            { label: t('testBuild.updatetime'), prop: 'updatetime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('testBuild.createtime'), prop: 'createtime', align: 'center', render: 'datetime', sortable: 'custom', operator: 'RANGE', width: 160 },
            { label: t('operate'), align: 'center', width: 140, render: 'buttons', buttons: optButtons, operator: false },
        ],
        dblClickNotEditColumn: [undefined, ],
		defaultOrder: { prop: 'weigh', order: 'desc' },
    },
    {
        defaultItems: {"views":"0","likes":"0","dislikes":"0","status":"1","weigh":"0"},
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
        name: 'testBuild',
    })
</script>

<style scoped lang="scss"></style>
