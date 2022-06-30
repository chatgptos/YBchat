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
				<FormItem :label="t('boothtem.stand')" type="string" v-model="baTable.form.items!.stand" prop="stand" :input-attr="{ placeholder: t('Please input field', { field: t('boothtem.stand') }) }" />
				<FormItem :label="t('boothtem.name')" type="string" v-model="baTable.form.items!.name" prop="name" :input-attr="{ placeholder: t('Please input field', { field: t('boothtem.name') }) }" />
				<FormItem :label="t('boothtem.area')" type="string" v-model="baTable.form.items!.area" prop="area" :input-attr="{ placeholder: t('Please input field', { field: t('boothtem.area') }) }" />
				<FormItem :label="t('boothtem.booth_height')" type="number" prop="booth_height" v-model.number="baTable.form.items!.booth_height" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothtem.booth_height') }) }" />
				<FormItem :label="t('boothtem.booth_width')" type="number" prop="booth_width" v-model.number="baTable.form.items!.booth_width" :input-attr="{ step: '1', placeholder: t('Please input field', { field: t('boothtem.booth_width') }) }" />
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
