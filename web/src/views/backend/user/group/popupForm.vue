<template>
    <!-- 对话框表单 -->
    <el-dialog
        custom-class="ba-operate-dialog"
        top="10vh"
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
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                >
                    <el-form-item prop="name" :label="t('user.group.Group name')">
                        <el-input
                            v-model="baTable.form.items!.name"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.group.Group name') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="auth" :label="t('user.group.jurisdiction')">
                        <el-tree
                            ref="treeRef"
                            :key="state.treeKey"
                            :default-checked-keys="state.defaultCheckedKeys"
                            :default-expand-all="true"
                            show-checkbox
                            node-key="id"
                            :props="{ children: 'children', label: 'title' }"
                            :data="state.menuRules"
                        />
                    </el-form-item>
                    <el-form-item :label="t('state')">
                        <el-radio v-model="baTable.form.items!.status" label="0" :border="true">{{ t('Disable') }}</el-radio>
                        <el-radio v-model="baTable.form.items!.status" label="1" :border="true">{{ t('Enable') }}</el-radio>
                    </el-form-item>
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
import { reactive, ref, watch, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import type baTableClass from '/@/utils/baTable'
import { getUserRules } from '/@/api/backend/user/group'
import type { ElForm, ElTree, FormItemRule } from 'element-plus'
import { uuid } from '/@/utils/random'

interface MenuRules {
    id: number
    title: string
    children: MenuRules[]
}

const treeRef = ref<InstanceType<typeof ElTree>>()
const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const state: {
    treeKey: string
    defaultCheckedKeys: number[]
    menuRules: MenuRules[]
} = reactive({
    treeKey: uuid(),
    defaultCheckedKeys: [],
    menuRules: [],
})

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    name: [
        {
            required: true,
            message: t('Please input field', { field: t('user.group.Group name') }),
            trigger: 'blur',
        },
    ],
    auth: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                let ids = getCheckeds()
                if (ids.length <= 0) {
                    return callback(new Error(t('Please select field', { field: t('user.group.jurisdiction') })))
                }
                return callback()
            },
        },
    ],
})

getUserRules().then((res) => {
    state.menuRules = res.data.list
})

const getCheckeds = () => {
    return treeRef.value!.getCheckedKeys().concat(treeRef.value!.getHalfCheckedKeys())
}

const getHalfCheckeds = () => {
    return treeRef.value!.getHalfCheckedKeys()
}

defineExpose({
    getCheckeds,
    getHalfCheckeds,
})

watch(
    () => baTable.form.items!.rules,
    () => {
        if (baTable.form.items!.rules && baTable.form.items!.rules.length) {
            if (baTable.form.items!.rules.includes('*')) {
                let arr: number[] = []
                for (const key in state.menuRules) {
                    arr.push(state.menuRules[key].id)
                }
                state.defaultCheckedKeys = arr
            } else {
                state.defaultCheckedKeys = baTable.form.items!.rules
            }
        } else {
            state.defaultCheckedKeys = []
        }
        state.treeKey = uuid()
    }
)
</script>

<style scoped lang="scss"></style>
