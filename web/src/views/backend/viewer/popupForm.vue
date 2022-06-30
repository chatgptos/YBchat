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
				<FormItem :label="t('viewer.viewer_name')" type="string" v-model="baTable.form.items!.viewer_name" prop="viewer_name" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.viewer_name') }) }" />
				<FormItem :label="t('viewer.job')" type="string" v-model="baTable.form.items!.job" prop="job" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.job') }) }" />
				<FormItem :label="t('viewer.company')" type="string" v-model="baTable.form.items!.company" prop="company" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.company') }) }" />
				<FormItem :label="t('viewer.country')" type="string" v-model="baTable.form.items!.country" prop="country" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.country') }) }" />
				<FormItem :label="t('viewer.email')" type="string" v-model="baTable.form.items!.email" prop="email" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.email') }) }" />
				<FormItem :label="t('viewer.tel')" type="string" v-model="baTable.form.items!.tel" prop="tel" :input-attr="{ placeholder: t('Please input field', { field: t('viewer.tel') }) }" />
				<FormItem :label="t('viewer.need')" type="number" prop="need" v-model.number="baTable.form.items!.need" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('viewer.need') }) }" />
				<FormItem :label="t('viewer.registrant_id')" type="remoteSelect" v-model="baTable.form.items!.registrant_id" prop="registrant_id" :input-attr="{ field: 'name', 'remote-url': 'registrant', pk: 'registrant.id', placeholder: t('Please select field', { field: t('viewer.registrant_id') }) }" />
				<FormItem :label="t('viewer.enimport')" type="number" prop="enimport" v-model.number="baTable.form.items!.enimport" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('viewer.enimport') }) }" />
				<FormItem :label="t('viewer.checkstate')" type="number" prop="checkstate" v-model.number="baTable.form.items!.checkstate" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('viewer.checkstate') }) }" />
				<FormItem :label="t('viewer.edit_time')" type="datetime" v-model="baTable.form.items!.edit_time" prop="edit_time" :input-attr="{ placeholder: t('Please select field', { field: t('viewer.edit_time') }) }" />
				<FormItem :label="t('viewer.is_download')" type="number" prop="is_download" v-model.number="baTable.form.items!.is_download" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('viewer.is_download') }) }" />
				<FormItem :label="t('viewer.is_export')" type="number" prop="is_export" v-model.number="baTable.form.items!.is_export" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('viewer.is_export') }) }" />
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

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
	edit_time: [buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('viewer.edit_time') }))],
})

</script>

<style scoped lang="scss"></style>
