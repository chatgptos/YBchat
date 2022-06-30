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
				<FormItem :label="t('article.cat_id')" type="remoteSelect" v-model="baTable.form.items!.cat_id" prop="cat_id" :input-attr="{ field: 'name', 'remote-url': 'cat', pk: 'cat.id', placeholder: t('Please select field', { field: t('article.cat_id') }) }" />
				<FormItem :label="t('article.title')" type="string" v-model="baTable.form.items!.title" prop="title" :input-attr="{ placeholder: t('Please input field', { field: t('article.title') }) }" />
				<FormItem :label="t('article.content')" type="editor" v-model="baTable.form.items!.content" prop="content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :input-attr="{ placeholder: t('Please input field', { field: t('article.content') }) }" />
				<FormItem :label="t('article.author')" type="string" v-model="baTable.form.items!.author" prop="author" :input-attr="{ placeholder: t('Please input field', { field: t('article.author') }) }" />
				<FormItem :label="t('article.author_email')" type="string" v-model="baTable.form.items!.author_email" prop="author_email" :input-attr="{ placeholder: t('Please input field', { field: t('article.author_email') }) }" />
				<FormItem :label="t('article.keywords')" type="string" v-model="baTable.form.items!.keywords" prop="keywords" :input-attr="{ placeholder: t('Please input field', { field: t('article.keywords') }) }" />
				<FormItem :label="t('article.article_type')" type="number" prop="article_type" v-model.number="baTable.form.items!.article_type" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('article.article_type') }) }" />
				<FormItem :label="t('article.is_open')" type="number" prop="is_open" v-model.number="baTable.form.items!.is_open" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('article.is_open') }) }" />
				<FormItem :label="t('article.add_time')" type="datetime" v-model="baTable.form.items!.add_time" prop="add_time" :input-attr="{ placeholder: t('Please select field', { field: t('article.add_time') }) }" />
				<FormItem :label="t('article.file_url')" type="string" v-model="baTable.form.items!.file_url" prop="file_url" :input-attr="{ placeholder: t('Please input field', { field: t('article.file_url') }) }" />
				<FormItem :label="t('article.open_type')" type="number" prop="open_type" v-model.number="baTable.form.items!.open_type" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('article.open_type') }) }" />
				<FormItem :label="t('article.link')" type="string" v-model="baTable.form.items!.link" prop="link" :input-attr="{ placeholder: t('Please input field', { field: t('article.link') }) }" />
				<FormItem :label="t('article.description')" type="string" v-model="baTable.form.items!.description" prop="description" :input-attr="{ placeholder: t('Please input field', { field: t('article.description') }) }" />
				<FormItem :label="t('article.order_num')" type="number" prop="order_num" v-model.number="baTable.form.items!.order_num" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('article.order_num') }) }" />
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
	cat_id: [buildValidatorData('required', t('article.cat_id'))],
	title: [buildValidatorData('required', t('article.title'))],
	content: [buildValidatorData('editorRequired', t('article.content'))],
	author: [buildValidatorData('required', t('article.author'))],
	author_email: [buildValidatorData('required', t('article.author_email'))],
	keywords: [buildValidatorData('required', t('article.keywords'))],
	article_type: [buildValidatorData('required', t('article.article_type'))],
	is_open: [buildValidatorData('required', t('article.is_open'))],
	add_time: [buildValidatorData('required', t('article.add_time')), buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('article.add_time') }))],
	file_url: [buildValidatorData('required', t('article.file_url'))],
	open_type: [buildValidatorData('required', t('article.open_type'))],
	link: [buildValidatorData('required', t('article.link'))],
})

</script>

<style scoped lang="scss"></style>
