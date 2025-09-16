<template>
    <div class="main-container">
        <section class="content">
            <main>
                <div class="content-main">
                    <h2 class="content-title">{{ t('login.Sign in to') + siteConfig.siteName }}</h2>
                    <el-form @keyup.enter="onSubmitPre" ref="formRef" :rules="rules" :model="form">
                        <!-- 账号 -->
                        <el-form-item prop="username" style="margin-bottom: 24px">
                            <label class="content-label">{{ t('login.Please enter an account') }}</label>
                            <el-input ref="usernameRef" v-model="form.username" clearable size="large"></el-input>
                        </el-form-item>
                        <!-- 密码 -->
                        <el-form-item prop="password" style="margin-bottom: 50px">
                            <label class="content-label">{{ t('login.Please input a password') }}</label>
                            <el-input ref="passwordRef" v-model="form.password" show-password size="large"></el-input>
                        </el-form-item>
                        <!-- 保持会话-->
                        <!-- <el-checkbox v-model="form.keep" :label="t('login.Hold session')" class="content-remenber"></el-checkbox> -->

                        <!-- 登录 -->
                        <el-form-item>
                            <el-button :loading="state.submitLoading" type="primary" size="large" class="content-login-btn" @click="onSubmitPre">
                                {{ t('login.Sign in') }}
                            </el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </main>
        </section>
    </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, reactive, ref } from 'vue'
import { uuid } from '/@/utils/random'
import { useSiteConfig } from '/@/stores/siteConfig'
import { buildValidatorData } from '/@/utils/validate'
import { ElForm, ElInput, InputInstance } from 'element-plus'
import { login } from '/@/api/backend'
import { useAdminInfo } from '/@/stores/adminInfo'
import router from '/@/router'
import { useI18n } from 'vue-i18n'
import { initialize } from '/@/api/frontend/index'
import clickCaptcha from '/@/components/clickCaptcha'
import { adminBaseRoutePath } from '/@/router/static/adminBase'

const adminInfo = useAdminInfo()
const siteConfig = useSiteConfig()

const { t } = useI18n()

const formRef = ref<InstanceType<typeof ElForm>>()
const usernameRef = ref<InputInstance>()
const passwordRef = ref<InputInstance>()

const state = reactive({
    showCaptcha: true,
    submitLoading: false,
})
const form = reactive({
    username: '',
    password: '',
    captcha: '',
    keep: false,
    captchaId: uuid(),
    captchaInfo: '',
})

// 表单验证规则
const rules = reactive({
    username: [buildValidatorData({ name: 'required', message: t('login.Please enter an account') }), buildValidatorData({ name: 'account' })],
    password: [buildValidatorData({ name: 'required', message: t('login.Please input a password') }), buildValidatorData({ name: 'password' })],
})

const focusInput = () => {
    if (form.username === '') {
        usernameRef.value!.focus()
    } else if (form.password === '') {
        passwordRef.value!.focus()
    }
}

const onSubmitPre = () => {
    formRef.value?.validate((valid) => {
        if (!valid) return
        if (state.showCaptcha) {
            clickCaptcha(form.captchaId, (captchaInfo: string) => onSubmit(captchaInfo))
        } else {
            onSubmit()
        }
    })
}

const onSubmit = (captchaInfo = '') => {
    state.submitLoading = true
    form.captchaInfo = captchaInfo
    login('post', form)
        .then((res) => {
            adminInfo.dataFill(res.data.userInfo, false)
            router.push({ path: adminBaseRoutePath })
        })
        .finally(() => {
            state.submitLoading = false
        })
}

onMounted(() => {
    initialize()

    login('get')
        .then((res) => {
            state.showCaptcha = res.data.captcha
            nextTick(() => focusInput())
        })
        .catch((err) => {
            console.log(err)
        })
})
</script>

<style scoped lang="scss">
* {
    margin: 0;
    padding: 0;
}

.content {
    height: 100vh;
    width: 100vw;
    background-image: url('/@/assets/login.png');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    background-position: center;

    main {
        .content-main {
            position: absolute;
            top: 50%;
            right: 240px;
            transform: translateY(-50%);
            padding: 48px;
            background: #fff;
            width: 416px;
            margin: auto;
            display: flex;
            flex-direction: column;
            gap: 48px;

            .content-title {
                font:
                    bold 24px/29px 'Haas Grot Text R Web',
                    'Helvetica Neue',
                    Helvetica,
                    Arial,
                    sans-serif;
            }

            .content-hr {
                margin-bottom: 17px;
                :deep(.el-divider__text) {
                    // #141414
                    background-color: var(--ba-bg-color);
                }
            }

            .content-label {
                color: var(--el-text-color-primary);
                font:
                    bold 14px/24px 'Haas Grot Text R Web',
                    'Helvetica Neue',
                    Helvetica,
                    Arial,
                    sans-serif;
            }

            .content-remenber {
                margin-top: 20px;
            }

            .content-captcha {
                display: flex;
                width: 100%;
                justify-content: space-between;

                .content-captcha-input {
                    max-width: 60%;
                }

                img {
                    border-radius: 3px;
                    max-width: 36%;
                }
            }

            .el-button {
                width: 100%;
            }
        }
    }
}
// 暗黑样式
@at-root .dark {
    .sidebar {
        filter: brightness(70%);
    }
}
</style>
