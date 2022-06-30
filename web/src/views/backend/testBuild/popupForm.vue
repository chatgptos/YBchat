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
				<FormItem :label="t('testBuild.title')" type="string" v-model="baTable.form.items!.title" prop="title" :input-attr="{ placeholder: t('Please input field', { field: t('testBuild.title') }) }" />
				<FormItem :label="t('testBuild.keyword_rows')" type="textarea" v-model="baTable.form.items!.keyword_rows" prop="keyword_rows" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('testBuild.keyword_rows') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('testBuild.content')" type="editor" v-model="baTable.form.items!.content" prop="content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :input-attr="{ placeholder: t('Please input field', { field: t('testBuild.content') }) }" />
				<FormItem :label="t('testBuild.views')" type="number" prop="views" v-model.number="baTable.form.items!.views" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('testBuild.views') }) }" />
				<FormItem :label="t('testBuild.likes')" type="number" prop="likes" v-model.number="baTable.form.items!.likes" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('testBuild.likes') }) }" />
				<FormItem :label="t('testBuild.dislikes')" type="number" prop="dislikes" v-model.number="baTable.form.items!.dislikes" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('testBuild.dislikes') }) }" />
				<FormItem :label="t('testBuild.note_textarea')" type="textarea" v-model="baTable.form.items!.note_textarea" prop="note_textarea" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('testBuild.note_textarea') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('testBuild.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :data="{ content: { 0: t('testBuild.status 0'), 1: t('testBuild.status 1') } }" :input-attr="{ placeholder: t('Please select field', { field: t('testBuild.status') }) }" />
				<FormItem :label="t('testBuild.weigh')" type="number" prop="weigh" v-model.number="baTable.form.items!.weigh" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('testBuild.weigh') }) }" />
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
	title: [buildValidatorData('required', t('testBuild.title'))],
	keyword_rows: [buildValidatorData('required', t('testBuild.keyword_rows'))],
	content: [buildValidatorData('editorRequired', t('testBuild.content'))],
	views: [buildValidatorData('required', t('testBuild.views'))],
	likes: [buildValidatorData('required', t('testBuild.likes'))],
	dislikes: [buildValidatorData('required', t('testBuild.dislikes'))],
	note_textarea: [buildValidatorData('required', t('testBuild.note_textarea'))],
	status: [buildValidatorData('required', t('testBuild.status'))],
	weigh: [buildValidatorData('required', t('testBuild.weigh'))],
})

</script>

<style scoped lang="scss"></style>
