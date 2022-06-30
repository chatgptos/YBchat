<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        :close-on-click-modal="false"
        :model-value="baTable.form.operate ? true : false"
        @close="baTable.toggleForm"
		width='50%'
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
				<FormItem :label="t('topic.name')" type="string" v-model="baTable.form.items!.name" prop="name" :input-attr="{ placeholder: t('Please input field', { field: t('topic.name') }) }" />
				<FormItem :label="t('topic.is_recommend')" type="number" prop="is_recommend" v-model.number="baTable.form.items!.is_recommend" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('topic.is_recommend') }) }" />
				<FormItem :label="t('topic.act_id')" type="remoteSelect" v-model="baTable.form.items!.act_id" prop="act_id" :input-attr="{ field: 'name', 'remote-url': 'act', pk: 'act.id', placeholder: t('Please select field', { field: t('topic.act_id') }) }" />
				<FormItem :label="t('topic.hall_id')" type="remoteSelect" v-model="baTable.form.items!.hall_id" prop="hall_id" :input-attr="{ field: 'name', 'remote-url': 'hall', pk: 'hall.id', placeholder: t('Please select field', { field: t('topic.hall_id') }) }" />
				<FormItem :label="t('topic.topic_content')" type="editor" v-model="baTable.form.items!.topic_content" prop="topic_content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :input-attr="{ placeholder: t('Please input field', { field: t('topic.topic_content') }) }" />
				<FormItem :label="t('topic.intro')" type="textarea" v-model="baTable.form.items!.intro" prop="intro" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('topic.intro') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('topic.start_time')" type="datetime" v-model="baTable.form.items!.start_time" prop="start_time" :input-attr="{ placeholder: t('Please select field', { field: t('topic.start_time') }) }" />
				<FormItem :label="t('topic.end_time')" type="datetime" v-model="baTable.form.items!.end_time" prop="end_time" :input-attr="{ placeholder: t('Please select field', { field: t('topic.end_time') }) }" />
				<FormItem :label="t('topic.data')" type="textarea" v-model="baTable.form.items!.data" prop="data" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('topic.data') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('topic.template')" type="string" v-model="baTable.form.items!.template" prop="template" :input-attr="{ placeholder: t('Please input field', { field: t('topic.template') }) }" />
				<FormItem :label="t('topic.css')" type="textarea" v-model="baTable.form.items!.css" prop="css" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('topic.css') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('topic.topic_img')" type="string" v-model="baTable.form.items!.topic_img" prop="topic_img" :input-attr="{ placeholder: t('Please input field', { field: t('topic.topic_img') }) }" />
				<FormItem :label="t('topic.title_img')" type="string" v-model="baTable.form.items!.title_img" prop="title_img" :input-attr="{ placeholder: t('Please input field', { field: t('topic.title_img') }) }" />
				<FormItem :label="t('topic.base_style')" type="string" v-model="baTable.form.items!.base_style" prop="base_style" :input-attr="{ placeholder: t('Please input field', { field: t('topic.base_style') }) }" />
				<FormItem :label="t('topic.htmls')" type="textarea" v-model="baTable.form.items!.htmls" prop="htmls" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('topic.htmls') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('topic.keywords')" type="string" v-model="baTable.form.items!.keywords" prop="keywords" :input-attr="{ placeholder: t('Please input field', { field: t('topic.keywords') }) }" />
				<FormItem :label="t('topic.description')" type="string" v-model="baTable.form.items!.description" prop="description" :input-attr="{ placeholder: t('Please input field', { field: t('topic.description') }) }" />
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
	name: [buildValidatorData('required', t('topic.name'))],
	hall_id: [buildValidatorData('required', t('topic.hall_id'))],
	intro: [buildValidatorData('required', t('topic.intro'))],
	start_time: [buildValidatorData('required', t('topic.start_time')), buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('topic.start_time') }))],
	end_time: [buildValidatorData('required', t('topic.end_time')), buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('topic.end_time') }))],
	data: [buildValidatorData('required', t('topic.data'))],
	template: [buildValidatorData('required', t('topic.template'))],
	css: [buildValidatorData('required', t('topic.css'))],
})

</script>

<style scoped lang="scss"></style>
