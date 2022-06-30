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
				<FormItem :label="t('exhibitor.exhibitor_id')" type="remoteSelect" v-model="baTable.form.items!.exhibitor_id" prop="exhibitor_id" :input-attr="{ field: 'name', 'remote-url': 'exhibitor', pk: 'exhibitor.id', placeholder: t('Please select field', { field: t('exhibitor.exhibitor_id') }) }" />
				<FormItem :label="t('exhibitor.name')" type="string" v-model="baTable.form.items!.name" prop="name" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.name') }) }" />
				<FormItem :label="t('exhibitor.company_name')" type="string" v-model="baTable.form.items!.company_name" prop="company_name" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.company_name') }) }" />
				<FormItem :label="t('exhibitor.company_name_en')" type="string" v-model="baTable.form.items!.company_name_en" prop="company_name_en" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.company_name_en') }) }" />
				<FormItem :label="t('exhibitor.contact_address')" type="string" v-model="baTable.form.items!.contact_address" prop="contact_address" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.contact_address') }) }" />
				<FormItem :label="t('exhibitor.contact_address_en')" type="string" v-model="baTable.form.items!.contact_address_en" prop="contact_address_en" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.contact_address_en') }) }" />
				<FormItem :label="t('exhibitor.linkman')" type="string" v-model="baTable.form.items!.linkman" prop="linkman" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.linkman') }) }" />
				<FormItem :label="t('exhibitor.linkman_en')" type="string" v-model="baTable.form.items!.linkman_en" prop="linkman_en" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.linkman_en') }) }" />
				<FormItem :label="t('exhibitor.contact_number')" type="number" prop="contact_number" v-model.number="baTable.form.items!.contact_number" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibitor.contact_number') }) }" />
				<FormItem :label="t('exhibitor.fax')" type="string" v-model="baTable.form.items!.fax" prop="fax" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.fax') }) }" />
				<FormItem :label="t('exhibitor.email')" type="string" v-model="baTable.form.items!.email" prop="email" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.email') }) }" />
				<FormItem :label="t('exhibitor.siturl')" type="string" v-model="baTable.form.items!.siturl" prop="siturl" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.siturl') }) }" />
				<FormItem :label="t('exhibitor.cellphone')" type="string" v-model="baTable.form.items!.cellphone" prop="cellphone" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.cellphone') }) }" />
				<FormItem :label="t('exhibitor.postcode')" type="string" v-model="baTable.form.items!.postcode" prop="postcode" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.postcode') }) }" />
				<FormItem :label="t('exhibitor.zh_introduce')" type="textarea" v-model="baTable.form.items!.zh_introduce" prop="zh_introduce" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('exhibitor.zh_introduce') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('exhibitor.en_introduce')" type="textarea" v-model="baTable.form.items!.en_introduce" prop="en_introduce" :input-attr="{ rows: 3, placeholder: t('Please input field', { field: t('exhibitor.en_introduce') }) }" @keyup.enter.stop="" @keyup.ctrl.enter="baTable.onSubmit(formRef)" />
				<FormItem :label="t('exhibitor.number')" type="number" prop="number" v-model.number="baTable.form.items!.number" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibitor.number') }) }" />
				<FormItem :label="t('exhibitor.phone_pre1')" type="string" v-model="baTable.form.items!.phone_pre1" prop="phone_pre1" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.phone_pre1') }) }" />
				<FormItem :label="t('exhibitor.phone_pre2')" type="string" v-model="baTable.form.items!.phone_pre2" prop="phone_pre2" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.phone_pre2') }) }" />
				<FormItem :label="t('exhibitor.fax_pre1')" type="string" v-model="baTable.form.items!.fax_pre1" prop="fax_pre1" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.fax_pre1') }) }" />
				<FormItem :label="t('exhibitor.fax_pre2')" type="string" v-model="baTable.form.items!.fax_pre2" prop="fax_pre2" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.fax_pre2') }) }" />
				<FormItem :label="t('exhibitor.boothNum')" type="number" prop="boothNum" v-model.number="baTable.form.items!.boothNum" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibitor.boothNum') }) }" />
				<FormItem :label="t('exhibitor.productServices')" type="string" v-model="baTable.form.items!.productServices" prop="productServices" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.productServices') }) }" />
				<FormItem :label="t('exhibitor.product_services_en')" type="string" v-model="baTable.form.items!.product_services_en" prop="product_services_en" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.product_services_en') }) }" />
				<FormItem :label="t('exhibitor.others')" type="string" v-model="baTable.form.items!.others" prop="others" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.others') }) }" />
				<FormItem :label="t('exhibitor.others_en')" type="string" v-model="baTable.form.items!.others_en" prop="others_en" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.others_en') }) }" />
				<FormItem :label="t('exhibitor.edit_user')" type="string" v-model="baTable.form.items!.edit_user" prop="edit_user" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.edit_user') }) }" />
				<FormItem :label="t('exhibitor.edit_time')" type="datetime" v-model="baTable.form.items!.edit_time" prop="edit_time" :input-attr="{ placeholder: t('Please select field', { field: t('exhibitor.edit_time') }) }" />
				<FormItem :label="t('exhibitor.company_name_en2zh')" type="string" v-model="baTable.form.items!.company_name_en2zh" prop="company_name_en2zh" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.company_name_en2zh') }) }" />
				<FormItem :label="t('exhibitor.approved_flag')" type="number" prop="approved_flag" v-model.number="baTable.form.items!.approved_flag" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('exhibitor.approved_flag') }) }" />
				<FormItem :label="t('exhibitor.approved_time')" type="datetime" v-model="baTable.form.items!.approved_time" prop="approved_time" :input-attr="{ placeholder: t('Please select field', { field: t('exhibitor.approved_time') }) }" />
				<FormItem :label="t('exhibitor.approved_ip')" type="string" v-model="baTable.form.items!.approved_ip" prop="approved_ip" :input-attr="{ placeholder: t('Please input field', { field: t('exhibitor.approved_ip') }) }" />
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
	edit_time: [buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('exhibitor.edit_time') }))],
	company_name_en2zh: [buildValidatorData('required', t('exhibitor.company_name_en2zh'))],
	approved_flag: [buildValidatorData('required', t('exhibitor.approved_flag'))],
	approved_time: [buildValidatorData('date', '', 'blur', t('Please enter the correct field', { field: t('exhibitor.approved_time') }))],
})

</script>

<style scoped lang="scss"></style>
