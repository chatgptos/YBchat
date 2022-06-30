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
				<FormItem :label="t('boothinfo.exhibition_id')" type="remoteSelect" v-model="baTable.form.items!.exhibition_id" prop="exhibition_id" :input-attr="{ field: 'name', 'remote-url': 'exhibition', pk: 'exhibition.id', placeholder: t('Please select field', { field: t('boothinfo.exhibition_id') }) }" />
				<FormItem :label="t('boothinfo.hall_id')" type="remoteSelect" v-model="baTable.form.items!.hall_id" prop="hall_id" :input-attr="{ field: 'name', 'remote-url': 'hall', pk: 'hall.id', placeholder: t('Please select field', { field: t('boothinfo.hall_id') }) }" />
				<FormItem :label="t('boothinfo.is_recommend')" type="number" prop="is_recommend" v-model.number="baTable.form.items!.is_recommend" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.is_recommend') }) }" />
				<FormItem :label="t('boothinfo.coordinate_x')" type="number" prop="coordinate_x" v-model.number="baTable.form.items!.coordinate_x" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.coordinate_x') }) }" />
				<FormItem :label="t('boothinfo.coordinate_y')" type="number" prop="coordinate_y" v-model.number="baTable.form.items!.coordinate_y" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.coordinate_y') }) }" />
				<FormItem :label="t('boothinfo.booth_widht')" type="number" prop="booth_widht" v-model.number="baTable.form.items!.booth_widht" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.booth_widht') }) }" />
				<FormItem :label="t('boothinfo.booth_height')" type="number" prop="booth_height" v-model.number="baTable.form.items!.booth_height" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.booth_height') }) }" />
				<FormItem :label="t('boothinfo.booth_area')" type="string" v-model="baTable.form.items!.booth_area" prop="booth_area" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_area') }) }" />
				<FormItem :label="t('boothinfo.boothtem_id')" type="remoteSelect" v-model="baTable.form.items!.boothtem_id" prop="boothtem_id" :input-attr="{ field: 'name', 'remote-url': 'boothtem', pk: 'boothtem.id', placeholder: t('Please select field', { field: t('boothinfo.boothtem_id') }) }" />
				<FormItem :label="t('boothinfo.boothtype_id')" type="remoteSelect" v-model="baTable.form.items!.boothtype_id" prop="boothtype_id" :input-attr="{ field: 'name', 'remote-url': 'boothtype', pk: 'boothtype.id', placeholder: t('Please select field', { field: t('boothinfo.boothtype_id') }) }" />
				<FormItem :label="t('boothinfo.booth_standard')" type="string" v-model="baTable.form.items!.booth_standard" prop="booth_standard" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_standard') }) }" />
				<FormItem :label="t('boothinfo.booth_type')" type="string" v-model="baTable.form.items!.booth_type" prop="booth_type" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_type') }) }" />
				<FormItem :label="t('boothinfo.booth_num')" type="number" prop="booth_num" v-model.number="baTable.form.items!.booth_num" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.booth_num') }) }" />
				<FormItem :label="t('boothinfo.booth_name')" type="string" v-model="baTable.form.items!.booth_name" prop="booth_name" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_name') }) }" />
				<FormItem :label="t('boothinfo.sales_status')" type="number" prop="sales_status" v-model.number="baTable.form.items!.sales_status" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.sales_status') }) }" />
				<FormItem :label="t('boothinfo.booth_tips')" type="string" v-model="baTable.form.items!.booth_tips" prop="booth_tips" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_tips') }) }" />
				<FormItem :label="t('boothinfo.company_name')" type="string" v-model="baTable.form.items!.company_name" prop="company_name" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.company_name') }) }" />
				<FormItem :label="t('boothinfo.category')" type="string" v-model="baTable.form.items!.category" prop="category" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.category') }) }" />
				<FormItem :label="t('boothinfo.country')" type="string" v-model="baTable.form.items!.country" prop="country" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.country') }) }" />
				<FormItem :label="t('boothinfo.state')" type="string" v-model="baTable.form.items!.state" prop="state" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.state') }) }" />
				<FormItem :label="t('boothinfo.addr')" type="string" v-model="baTable.form.items!.addr" prop="addr" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.addr') }) }" />
				<FormItem :label="t('boothinfo.moble_phone')" type="string" v-model="baTable.form.items!.moble_phone" prop="moble_phone" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.moble_phone') }) }" />
				<FormItem :label="t('boothinfo.phone')" type="string" v-model="baTable.form.items!.phone" prop="phone" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.phone') }) }" />
				<FormItem :label="t('boothinfo.email')" type="string" v-model="baTable.form.items!.email" prop="email" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.email') }) }" />
				<FormItem :label="t('boothinfo.webaddr')" type="string" v-model="baTable.form.items!.webaddr" prop="webaddr" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.webaddr') }) }" />
				<FormItem :label="t('boothinfo.contacts')" type="string" v-model="baTable.form.items!.contacts" prop="contacts" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.contacts') }) }" />
				<FormItem :label="t('boothinfo.fax')" type="string" v-model="baTable.form.items!.fax" prop="fax" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.fax') }) }" />
				<FormItem :label="t('boothinfo.position')" type="string" v-model="baTable.form.items!.position" prop="position" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.position') }) }" />
				<FormItem :label="t('boothinfo.open_angle')" type="number" prop="open_angle" v-model.number="baTable.form.items!.open_angle" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.open_angle') }) }" />
				<FormItem :label="t('boothinfo.booth_discount')" type="number" prop="booth_discount" v-model.number="baTable.form.items!.booth_discount" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.booth_discount') }) }" />
				<FormItem :label="t('boothinfo.sales_distribution')" type="string" v-model="baTable.form.items!.sales_distribution" prop="sales_distribution" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.sales_distribution') }) }" />
				<FormItem :label="t('boothinfo.booth_price')" type="string" v-model="baTable.form.items!.booth_price" prop="booth_price" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_price') }) }" />
				<FormItem :label="t('boothinfo.angle_add')" type="string" v-model="baTable.form.items!.angle_add" prop="angle_add" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.angle_add') }) }" />
				<FormItem :label="t('boothinfo.booth_amount')" type="string" v-model="baTable.form.items!.booth_amount" prop="booth_amount" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.booth_amount') }) }" />
				<FormItem :label="t('boothinfo.build_state')" type="string" v-model="baTable.form.items!.build_state" prop="build_state" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.build_state') }) }" />
				<FormItem :label="t('boothinfo.china_abbreviate')" type="string" v-model="baTable.form.items!.china_abbreviate" prop="china_abbreviate" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.china_abbreviate') }) }" />
				<FormItem :label="t('boothinfo.english_abbreviate')" type="string" v-model="baTable.form.items!.english_abbreviate" prop="english_abbreviate" :input-attr="{ placeholder: t('Please input field', { field: t('boothinfo.english_abbreviate') }) }" />
				<FormItem :label="t('boothinfo.xiongk_num_free')" type="number" prop="xiongk_num_free" v-model.number="baTable.form.items!.xiongk_num_free" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.xiongk_num_free') }) }" />
				<FormItem :label="t('boothinfo.xiongk_num_change')" type="number" prop="xiongk_num_change" v-model.number="baTable.form.items!.xiongk_num_change" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.xiongk_num_change') }) }" />
				<FormItem :label="t('boothinfo.is_assigned')" type="number" prop="is_assigned" v-model.number="baTable.form.items!.is_assigned" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.is_assigned') }) }" />
				<FormItem :label="t('boothinfo.imp_buyernum')" type="number" prop="imp_buyernum" v-model.number="baTable.form.items!.imp_buyernum" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.imp_buyernum') }) }" />
				<FormItem :label="t('boothinfo.upload_batch')" type="number" prop="upload_batch" v-model.number="baTable.form.items!.upload_batch" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothinfo.upload_batch') }) }" />
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
