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
				<FormItem :label="t('ticket.user_id')" type="remoteSelect" v-model="baTable.form.items!.user_id" prop="user_id" :input-attr="{ field: 'nickname', 'remote-url': 'user', pk: 'user.id', placeholder: t('Please select field', { field: t('ticket.user_id') }) }" />
				<FormItem :label="t('ticket.topic_id')" type="remoteSelect" v-model="baTable.form.items!.topic_id" prop="topic_id" :input-attr="{ field: 'name', 'remote-url': 'topic', pk: 'topic.id', placeholder: t('Please select field', { field: t('ticket.topic_id') }) }" />
				<FormItem :label="t('ticket.exhibition_id')" type="remoteSelect" v-model="baTable.form.items!.exhibition_id" prop="exhibition_id" :input-attr="{ field: 'name', 'remote-url': 'exhibition', pk: 'exhibition.id', placeholder: t('Please select field', { field: t('ticket.exhibition_id') }) }" />
				<FormItem :label="t('ticket.ticket_qr_code_url_img')" type="string" v-model="baTable.form.items!.ticket_qr_code_url_img" prop="ticket_qr_code_url_img" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.ticket_qr_code_url_img') }) }" />
				<FormItem :label="t('ticket.ticket_desc')" type="string" v-model="baTable.form.items!.ticket_desc" prop="ticket_desc" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.ticket_desc') }) }" />
				<FormItem :label="t('ticket.type')" type="number" prop="type" v-model.number="baTable.form.items!.type" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('ticket.type') }) }" />
				<FormItem :label="t('ticket.status')" type="number" prop="status" v-model.number="baTable.form.items!.status" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('ticket.status') }) }" />
				<FormItem :label="t('ticket.apply_time')" type="string" v-model="baTable.form.items!.apply_time" prop="apply_time" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.apply_time') }) }" />
				<FormItem :label="t('ticket.enable_time')" type="string" v-model="baTable.form.items!.enable_time" prop="enable_time" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.enable_time') }) }" />
				<FormItem :label="t('ticket.views')" type="number" prop="views" v-model.number="baTable.form.items!.views" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('ticket.views') }) }" />
				<FormItem :label="t('ticket.address')" type="string" v-model="baTable.form.items!.address" prop="address" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.address') }) }" />
				<FormItem :label="t('ticket.ticket_fee')" type="number" prop="ticket_fee" v-model.number="baTable.form.items!.ticket_fee" :input-attr="{ step: '0.01', placeholder: t('Please input field', { field: t('ticket.ticket_fee') }) }" />
				<FormItem :label="t('ticket.ticket_name')" type="string" v-model="baTable.form.items!.ticket_name" prop="ticket_name" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.ticket_name') }) }" />
				<FormItem :label="t('ticket.ticket_img')" type="string" v-model="baTable.form.items!.ticket_img" prop="ticket_img" :input-attr="{ placeholder: t('Please input field', { field: t('ticket.ticket_img') }) }" />
				<FormItem :label="t('ticket.free_money')" type="number" prop="free_money" v-model.number="baTable.form.items!.free_money" :input-attr="{ step: '0.01', placeholder: t('Please input field', { field: t('ticket.free_money') }) }" />
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
	ticket_desc: [buildValidatorData('required', t('ticket.ticket_desc'))],
	ticket_fee: [buildValidatorData('required', t('ticket.ticket_fee'))],
	ticket_name: [buildValidatorData('required', t('ticket.ticket_name'))],
	ticket_img: [buildValidatorData('required', t('ticket.ticket_img'))],
	free_money: [buildValidatorData('required', t('ticket.free_money'))],
})

</script>

<style scoped lang="scss"></style>
