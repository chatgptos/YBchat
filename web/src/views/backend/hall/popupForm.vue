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
				<FormItem :label="t('hall.exhibition_id')" type="remoteSelect" v-model="baTable.form.items!.exhibition_id" prop="exhibition_id" :input-attr="{ field: 'name', 'remote-url': 'exhibition', pk: 'exhibition.id', placeholder: t('Please select field', { field: t('hall.exhibition_id') }) }" />
				<FormItem :label="t('hall.hall_name')" type="string" v-model="baTable.form.items!.hall_name" prop="hall_name" :input-attr="{ placeholder: t('Please input field', { field: t('hall.hall_name') }) }" />
				<FormItem :label="t('hall.name')" type="string" v-model="baTable.form.items!.name" prop="name" :input-attr="{ placeholder: t('Please input field', { field: t('hall.name') }) }" />
				<FormItem :label="t('hall.booths_num')" type="number" prop="booths_num" v-model.number="baTable.form.items!.booths_num" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.booths_num') }) }" />
				<FormItem :label="t('hall.hall_map')" type="string" v-model="baTable.form.items!.hall_map" prop="hall_map" :input-attr="{ placeholder: t('Please input field', { field: t('hall.hall_map') }) }" />
				<FormItem :label="t('hall.hall_addr')" type="string" v-model="baTable.form.items!.hall_addr" prop="hall_addr" :input-attr="{ placeholder: t('Please input field', { field: t('hall.hall_addr') }) }" />
				<FormItem :label="t('hall.map_height')" type="number" prop="map_height" v-model.number="baTable.form.items!.map_height" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.map_height') }) }" />
				<FormItem :label="t('hall.modified')" type="number" prop="modified" v-model.number="baTable.form.items!.modified" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.modified') }) }" />
				<FormItem :label="t('hall.created')" type="number" prop="created" v-model.number="baTable.form.items!.created" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.created') }) }" />
				<FormItem :label="t('hall.map_width')" type="number" prop="map_width" v-model.number="baTable.form.items!.map_width" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.map_width') }) }" />
				<FormItem :label="t('hall.is_recommend')" type="number" prop="is_recommend" v-model.number="baTable.form.items!.is_recommend" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('hall.is_recommend') }) }" />
				<FormItem :label="t('hall.hall_namein')" type="string" v-model="baTable.form.items!.hall_namein" prop="hall_namein" :input-attr="{ placeholder: t('Please input field', { field: t('hall.hall_namein') }) }" />
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
	modified: [buildValidatorData('required', t('hall.modified'))],
	created: [buildValidatorData('required', t('hall.created'))],
})

</script>

<style scoped lang="scss"></style>
