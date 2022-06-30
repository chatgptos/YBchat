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
				<FormItem :label="t('flBoothUserCollect.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ field: 'nickname', 'remote-url': 'user', pk: 'user.id', placeholder: t('Please select field', { field: t('flBoothUserCollect.user_id') }) }" />
				<FormItem :label="t('flBoothUserCollect.exhibitor_id')" type="remoteSelect" v-model="baTable.form.items!.exhibitor_id" prop="exhibitor_id" :input-attr="{ field: 'name', 'remote-url': 'exhibitor', pk: 'exhibitor.id', placeholder: t('Please select field', { field: t('flBoothUserCollect.exhibitor_id') }) }" />
				<FormItem :label="t('flBoothUserCollect.activity_id')" type="remoteSelect" v-model="baTable.form.items!.activity_id" prop="activity_id" :input-attr="{ field: 'name', 'remote-url': 'activity', pk: 'activity.id', placeholder: t('Please select field', { field: t('flBoothUserCollect.activity_id') }) }" />
				<FormItem :label="t('flBoothUserCollect.goods_id')" type="remoteSelect" v-model="baTable.form.items!.goods_id" prop="goods_id" :input-attr="{ field: 'name', 'remote-url': 'goods', pk: 'goods.id', placeholder: t('Please select field', { field: t('flBoothUserCollect.goods_id') }) }" />
				<FormItem :label="t('flBoothUserCollect.add_time')" type="datetime" v-model="baTable.form.items!.add_time" prop="add_time" :input-attr="{ placeholder: t('Please select field', { field: t('flBoothUserCollect.add_time') }) }" />
				<FormItem :label="t('flBoothUserCollect.topic_id')" type="remoteSelect" v-model="baTable.form.items!.topic_id" prop="topic_id" :input-attr="{ field: 'name', 'remote-url': 'topic', pk: 'topic.id', placeholder: t('Please select field', { field: t('flBoothUserCollect.topic_id') }) }" />
				<FormItem :label="t('flBoothUserCollect.is_attention')" type="number" prop="is_attention" v-model.number="baTable.form.items!.is_attention" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('flBoothUserCollect.is_attention') }) }" />
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
	user_id: [buildValidatorData('required', t('flBoothUserCollect.user_id'))],
	exhibitor_id: [buildValidatorData('required', t('flBoothUserCollect.exhibitor_id'))],
	goods_id: [buildValidatorData('required', t('flBoothUserCollect.goods_id'))],
	add_time: [buildValidatorData('required', t('flBoothUserCollect.add_time')), buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('flBoothUserCollect.add_time') }))],
	is_attention: [buildValidatorData('required', t('flBoothUserCollect.is_attention'))],
})

</script>

<style scoped lang="scss"></style>
