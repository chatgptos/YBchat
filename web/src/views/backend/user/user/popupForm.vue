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
                    ref="formRef"
                    @keyup.enter="baTable.onSubmit(formRef)"
                    :model="baTable.form.items"
                    label-position="right"
                    :label-width="baTable.form.labelWidth + 'px'"
                    :rules="rules"
                    v-if="!baTable.form.loading"
                >
                    <el-form-item prop="username" :label="t('user.user.User name')">
                        <el-input
                            v-model="baTable.form.items!.username"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.User name') + '(' + t('user.user.Login account') + ')' })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="nickname" :label="t('user.user.nickname')">
                        <el-input
                            v-model="baTable.form.items!.nickname"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.nickname') })"
                        ></el-input>
                    </el-form-item>
                    <FormItem
                        type="remoteSelect"
                        :label="t('user.user.grouping')"
                        v-model="baTable.form.items!.group_id"
                        :placeholder="t('user.user.grouping')"
                        :input-attr="{
                            params: { isTree: true },
                            field: 'name',
                            'remote-url': userGroup + 'index',
                        }"
                    />
                    <el-form-item :label="t('user.user.head portrait')">
                        <el-upload
                            class="avatar-uploader"
                            action=""
                            :show-file-list="false"
                            @change="onAvatarBeforeUpload"
                            :auto-upload="false"
                            accept="image/gif, image/jpg, image/jpeg, image/bmp, image/png, image/webp"
                        >
                            <el-image :src="baTable.form.items!.avatar" class="avatar">
                                <template #error>
                                    <div class="image-slot">
                                        <Icon size="30" color="#c0c4cc" name="el-icon-Picture" />
                                    </div>
                                </template>
                            </el-image>
                        </el-upload>
                    </el-form-item>
                    <el-form-item prop="email" :label="t('user.user.mailbox')">
                        <el-input
                            v-model="baTable.form.items!.email"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.mailbox') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="mobile" :label="t('user.user.mobile')">
                        <el-input
                            v-model="baTable.form.items!.mobile"
                            type="string"
                            :placeholder="t('Please input field', { field: t('user.user.mobile') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('user.user.Gender')">
                        <el-radio v-model="baTable.form.items!.gender" :label="0" :border="true">{{ t('unknown') }}</el-radio>
                        <el-radio v-model="baTable.form.items!.gender" :label="1" :border="true">{{ t('user.user.male') }}</el-radio>
                        <el-radio v-model="baTable.form.items!.gender" :label="2" :border="true">{{ t('user.user.female') }}</el-radio>
                    </el-form-item>
                    <el-form-item :label="t('user.user.birthday')">
                        <el-date-picker
                            class="w100"
                            value-format="YYYY-MM-DD"
                            v-model="baTable.form.items!.birthday"
                            type="date"
                            :placeholder="t('Please select field', { field: t('user.user.birthday') })"
                        />
                    </el-form-item>
                    <el-form-item v-if="baTable.form.operate == 'edit'" :label="t('user.user.balance')">
                        <el-input v-model="baTable.form.items!.money" readonly>
                            <template #append>
                                <el-button @click="changeAccount('money')">{{ t('user.user.Adjustment balance') }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item v-if="baTable.form.operate == 'edit'" :label="t('user.user.integral')">
                        <el-input v-model="baTable.form.items!.score" readonly>
                            <template #append>
                                <el-button @click="changeAccount('score')">{{ t('user.user.Adjust integral') }}</el-button>
                            </template>
                        </el-input>
                    </el-form-item>
                    <el-form-item prop="password" :label="t('user.user.password')">
                        <el-input
                            v-model="baTable.form.items!.password"
                            type="password"
                            :placeholder="
                                baTable.form.operate == 'add'
                                    ? t('Please input field', { field: t('user.user.password') })
                                    : t('user.user.Please leave blank if not modified')
                            "
                        ></el-input>
                    </el-form-item>
                    <el-form-item prop="motto" :label="t('user.user.Personal signature')">
                        <el-input
                            @keyup.enter.stop=""
                            @keyup.ctrl.enter="baTable.onSubmit(formRef)"
                            v-model="baTable.form.items!.motto"
                            type="textarea"
                            :placeholder="t('Please input field', { field: t('user.user.Personal signature') })"
                        ></el-input>
                    </el-form-item>
                    <el-form-item :label="t('state')">
                        <el-radio v-model="baTable.form.items!.status" label="disable" :border="true">{{ t('Disable') }}</el-radio>
                        <el-radio v-model="baTable.form.items!.status" label="enable" :border="true">{{ t('Enable') }}</el-radio>
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
import { ref, reactive, inject } from 'vue'
import { useI18n } from 'vue-i18n'
import { fileUpload } from '/@/api/common'
import type baTableClass from '/@/utils/baTable'
import { regularPassword, validatorAccount, validatorMobile } from '/@/utils/validate'
import type { ElForm, FormItemRule } from 'element-plus'
import FormItem from '/@/components/formItem/index.vue'
import { userGroup } from '/@/api/controllerUrls'
import router from '/@/router/index'

const formRef = ref<InstanceType<typeof ElForm>>()
const baTable = inject('baTable') as baTableClass

const { t } = useI18n()

const rules: Partial<Record<string, FormItemRule[]>> = reactive({
    username: [
        {
            required: true,
            message: t('Please input field', { field: t('user.user.User name') }),
            trigger: 'blur',
        },
        {
            validator: validatorAccount,
            trigger: 'blur',
        },
    ],
    nickname: [
        {
            required: true,
            message: t('Please input field', { field: t('user.user.nickname') }),
            trigger: 'blur',
        },
    ],
    email: [
        {
            type: 'email',
            message: t('Please enter the correct field', { field: t('user.user.mailbox') }),
            trigger: 'blur',
        },
    ],
    mobile: [
        {
            validator: validatorMobile,
            trigger: 'blur',
        },
    ],
    password: [
        {
            validator: (rule: any, val: string, callback: Function) => {
                if (baTable.form.operate == 'add') {
                    if (!val) {
                        return callback(new Error(t('Please input field', { field: t('user.user.password') })))
                    }
                } else {
                    if (!val) {
                        return callback()
                    }
                }
                if (!regularPassword(val)) {
                    return callback(new Error(t('Please enter the correct field', { field: t('user.user.password') })))
                }
                return callback()
            },
            trigger: 'blur',
        },
    ],
})

const onAvatarBeforeUpload = (file: any) => {
    let fd = new FormData()
    fd.append('file', file.raw)
    fileUpload(fd).then((res) => {
        if (res.code == 1) {
            baTable.form.items!.avatar = res.data.file.full_url
        }
    })
}

const changeAccount = (type: string) => {
    baTable.toggleForm()
    router.push({
        name: type == 'money' ? 'user/moneyLog' : 'user/scoreLog',
        query: {
            user_id: baTable.form.items!.id,
        },
    })
}
</script>

<style scoped lang="scss">
.avatar-uploader {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    border-radius: var(--el-border-radius-small);
    box-shadow: var(--el-box-shadow-light);
    border: 1px dashed var(--color-sub-1);
    cursor: pointer;
    overflow: hidden;
    width: 110px;
    height: 110px;
}
.avatar-uploader:hover {
    border-color: var(--color-primary);
}
.avatar {
    width: 110px;
    height: 110px;
    display: block;
}
.image-slot {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
}
</style>
