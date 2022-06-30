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
				<FormItem :label="t('advert.admin_id')" type="remoteSelect" v-model="baTable.form.items!.admin_id" prop="admin_id" :input-attr="{ field: 'nickname', 'remote-url': 'admin', pk: 'admin.id', placeholder: t('Please select field', { field: t('advert.admin_id') }) }" />
				<FormItem :label="t('advert.module')" type="radio" v-model="baTable.form.items!.module" prop="module" :data="{ content: { open: t('advert.module open'), page: t('advert.module page'), category: t('advert.module category'), first: t('advert.module first'), other: t('advert.module other') } }" :input-attr="{ placeholder: t('Please select field', { field: t('advert.module') }) }" />
				<FormItem :label="t('advert.category_id')" type="remoteSelect" v-model="baTable.form.items!.category_id" prop="category_id" :input-attr="{ field: 'name', 'remote-url': 'category', pk: 'category.id', placeholder: t('Please select field', { field: t('advert.category_id') }) }" />
				<FormItem :label="t('advert.type')" type="radio" v-model="baTable.form.items!.type" prop="type" :data="{ content: { banner: t('advert.type banner'), image: t('advert.type image'), video: t('advert.type video') } }" :input-attr="{ placeholder: t('Please select field', { field: t('advert.type') }) }" />
				<FormItem :label="t('advert.media')" type="string" v-model="baTable.form.items!.media" prop="media" :input-attr="{ placeholder: t('Please input field', { field: t('advert.media') }) }" />
				<FormItem :label="t('advert.url')" type="string" v-model="baTable.form.items!.url" prop="url" :input-attr="{ placeholder: t('Please input field', { field: t('advert.url') }) }" />
				<FormItem :label="t('advert.title')" type="string" v-model="baTable.form.items!.title" prop="title" :input-attr="{ placeholder: t('Please input field', { field: t('advert.title') }) }" />
				<FormItem :label="t('advert.content')" type="editor" v-model="baTable.form.items!.content" prop="content" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" :input-attr="{ placeholder: t('Please input field', { field: t('advert.content') }) }" />
				<FormItem :label="t('advert.city')" type="city" v-model="baTable.form.items!.city" prop="city" :input-attr="{ placeholder: t('Please select field', { field: t('advert.city') }) }" />
				<FormItem :label="t('advert.startdate')" type="date" v-model="baTable.form.items!.startdate" prop="startdate" :input-attr="{ placeholder: t('Please select field', { field: t('advert.startdate') }) }" />
				<FormItem :label="t('advert.enddate')" type="date" v-model="baTable.form.items!.enddate" prop="enddate" :input-attr="{ placeholder: t('Please select field', { field: t('advert.enddate') }) }" />
				<FormItem :label="t('advert.views')" type="number" prop="views" v-model.number="baTable.form.items!.views" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.views') }) }" />
				<FormItem :label="t('advert.show')" type="number" prop="show" v-model.number="baTable.form.items!.show" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.show') }) }" />
				<FormItem :label="t('advert.created')" type="number" prop="created" v-model.number="baTable.form.items!.created" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.created') }) }" />
				<FormItem :label="t('advert.modified')" type="number" prop="modified" v-model.number="baTable.form.items!.modified" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.modified') }) }" />
				<FormItem :label="t('advert.deleted')" type="number" prop="deleted" v-model.number="baTable.form.items!.deleted" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.deleted') }) }" />
				<FormItem :label="t('advert.weigh')" type="number" prop="weigh" v-model.number="baTable.form.items!.weigh" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('advert.weigh') }) }" />
				<FormItem :label="t('advert.status')" type="radio" v-model="baTable.form.items!.status" prop="status" :data="{ content: { normal: t('advert.normal'), hidden: t('advert.hidden') } }" :input-attr="{ placeholder: t('Please select field', { field: t('advert.status') }) }" />
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
	admin_id: [buildValidatorData('required', t('advert.admin_id'))],
	module: [buildValidatorData('required', t('advert.module'))],
	category_id: [buildValidatorData('required', t('advert.category_id'))],
	type: [buildValidatorData('required', t('advert.type'))],
	media: [buildValidatorData('required', t('advert.media'))],
	url: [buildValidatorData('required', t('advert.url'))],
	title: [buildValidatorData('required', t('advert.title'))],
	content: [buildValidatorData('editorRequired', t('advert.content'))],
	city: [buildValidatorData('required', t('advert.city'))],
	startdate: [buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('advert.startdate') }))],
	enddate: [buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('advert.enddate') }))],
	views: [buildValidatorData('required', t('advert.views'))],
	show: [buildValidatorData('required', t('advert.show'))],
	weigh: [buildValidatorData('required', t('advert.weigh'))],
})

</script>

<style scoped lang="scss"></style>
