<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
    >
        <template #header>
            <div class="title" v-drag="['.ba-operate-dialog', '.el-dialog__header']" v-zoom="'.ba-operate-dialog'">
                {{ baTable.form.operate ? t(baTable.form.operate) : '' }}
            </div>
        </template>
        <el-scrollbar v-loading="baTable.form.loading" class="ba-table-form-scrollbar">
            <div
                class="ba-operate-form"
                :class="'ba-' + baTable.form.operate + '-form'"
                :style="'width: calc(100% - ' + baTable.form.labelWidth! / 2 + 'px)'"
            >
                <el-form
                    v-if="!baTable.form.loading"
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
				<FormItem :label="t('exhibition.exhibition_name')" type="string" v-model="baTable.form.items!.exhibition_name" prop="exhibition_name" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.exhibition_name') }) }" />
				<FormItem :label="t('exhibition.name')" type="string" v-model="baTable.form.items!.name" prop="name" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.name') }) }" />
				<FormItem :label="t('exhibition.business_services_img')" type="string" v-model="baTable.form.items!.business_services_img" prop="business_services_img" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.business_services_img') }) }" />
				<FormItem :label="t('exhibition.traffic_img')" type="string" v-model="baTable.form.items!.traffic_img" prop="traffic_img" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.traffic_img') }) }" />
				<FormItem :label="t('exhibition.service')" type="string" v-model="baTable.form.items!.service" prop="service" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.service') }) }" />
				<FormItem :label="t('exhibition.start_time')" type="string" v-model="baTable.form.items!.start_time" prop="start_time" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.start_time') }) }" />
				<FormItem :label="t('exhibition.end_time')" type="string" v-model="baTable.form.items!.end_time" prop="end_time" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.end_time') }) }" />
				<FormItem :label="t('exhibition.status')" type="number" prop="status" v-model.number="baTable.form.items!.status" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibition.status') }) }" />
				<FormItem :label="t('exhibition.remark')" type="string" v-model="baTable.form.items!.remark" prop="remark" :input-attr="{ placeholder: t('Please input field', { field: t('exhibition.remark') }) }" />
				<FormItem :label="t('exhibition.modified')" type="number" prop="modified" v-model.number="baTable.form.items!.modified" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibition.modified') }) }" />
				<FormItem :label="t('exhibition.created')" type="number" prop="created" v-model.number="baTable.form.items!.created" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibition.created') }) }" />
				<FormItem :label="t('exhibition.deleted')" type="number" prop="deleted" v-model.number="baTable.form.items!.deleted" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibition.deleted') }) }" />
                </el-form>
            </div>
        </el-scrollbar>
        <template #footer>
            <div :style="'width: calc(100% - ' + baTable.form.labelWidth! / 1.8 + 'px)'">
                <el-button @click="baTable.toggleForm('')">{{ t('Cancel') }}</el-button>
                <el-button v-blur :loading="baTable.form.submitLoading" @click="baTable.onSubmit(formRef)" type="primary">
                    {{ baTable.form.operateIds && baTable.form.operateIds.length > 1 ? t('Save and edit next item') : t('Save') }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<script setup lang="ts">
import { reactive, ref, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import FormItem from '/@/components/formItem/index.vue'
import type { ElForm, FormItemRule } from 'element-plus'
import { buildValidatorData } from '/@/utils/validate'


const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({})

</script>

<style scoped lang="scss"></style>
