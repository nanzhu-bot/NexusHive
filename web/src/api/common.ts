import createAxios from '/@/utils/axios'
import { isAdminApp, checkFileMimetype } from '/@/utils/common'
import { getUrl } from '/@/utils/axios'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useUserInfo } from '/@/stores/userInfo'
import { ElNotification, type UploadRawFile } from 'element-plus'
import { useSiteConfig } from '/@/stores/siteConfig'
import { state as uploadExpandState, fileUpload as uploadExpand } from '/@/components/mixins/baUpload'
import type { AxiosRequestConfig } from 'axios'
import { uuid } from '/@/utils/random'
import { i18n } from '../lang'
import { adminBaseRoutePath } from '/@/router/static/adminBase'
import { SYSTEM_ZINDEX } from '/@/stores/constant/common'

/*
 * 公共请求函数和Url定义
 */

// Admin模块
export const adminUploadUrl = '/admin/ajax/upload'
export const adminBuildSuffixSvgUrl = adminBaseRoutePath + '/ajax/buildSuffixSvg'
export const adminAreaUrl = '/admin/ajax/area'
export const getTablePkUrl = '/admin/ajax/getTablePk'
export const getTableListUrl = '/admin/ajax/getTableList'
export const getTableFieldListUrl = '/admin/ajax/getTableFieldList'
export const getDatabaseConnectionListUrl = '/admin/ajax/getDatabaseConnectionList'
export const terminalUrl = adminBaseRoutePath + '/ajax/terminal'
export const changeTerminalConfigUrl = '/admin/ajax/changeTerminalConfig'
export const clearCacheUrl = '/admin/ajax/clearCache'

// 公共
export const captchaUrl = '/api/common/captcha'
export const clickCaptchaUrl = '/api/common/clickCaptcha'
export const checkClickCaptchaUrl = '/api/common/checkClickCaptcha'
export const refreshTokenUrl = '/api/common/refreshToken'

// api模块(前台)
export const apiUploadUrl = '/api/ajax/upload'
export const apiBuildSuffixSvgUrl = '/api/ajax/buildSuffixSvg'
export const apiAreaUrl = '/api/ajax/area'
export const apiSendSms = '/api/Sms/send'
export const apiSendEms = '/api/Ems/send'

/**
 * 上传文件
 */
export function fileUpload(fd: FormData, params: anyObj = {}, forceLocal = false, config: AxiosRequestConfig = {}): ApiPromise {
    let errorMsg = ''
    const file = fd.get('file') as UploadRawFile
    const siteConfig = useSiteConfig()

    if (!file.name || typeof file.size == 'undefined') {
        errorMsg = i18n.global.t('utils.The data of the uploaded file is incomplete!')
    } else if (!checkFileMimetype(file.name, file.type)) {
        errorMsg = i18n.global.t('utils.The type of uploaded file is not allowed!')
    } else if (file.size > siteConfig.upload.maxSize) {
        errorMsg = i18n.global.t('utils.The size of the uploaded file exceeds the allowed range!')
    }
    if (errorMsg) {
        return new Promise((resolve, reject) => {
            ElNotification({
                type: 'error',
                message: errorMsg,
                zIndex: SYSTEM_ZINDEX,
            })
            reject(errorMsg)
        })
    }

    if (!forceLocal && uploadExpandState() == 'enable') {
        return uploadExpand(fd, params, config)
    }

    return createAxios({
        url: isAdminApp() ? adminUploadUrl : apiUploadUrl,
        method: 'POST',
        data: fd,
        params: params,
        timeout: 0,
        ...config,
    })
}

/**
 * 生成文件后缀icon的svg图片
 * @param suffix 后缀名
 * @param background 背景色,如:rgb(255,255,255)
 */
export function buildSuffixSvgUrl(suffix: string, background = '') {
    const adminInfo = useAdminInfo()
    return (
        getUrl() +
        (isAdminApp() ? adminBuildSuffixSvgUrl : apiBuildSuffixSvgUrl) +
        '?batoken=' +
        adminInfo.getToken() +
        '&suffix=' +
        suffix +
        (background ? '&background=' + background : '') +
        '&server=1'
    )
}

/**
 * 获取地区数据
 */
export function getArea(values: number[]) {
    const params: { province?: number; city?: number; uuid?: string } = {}
    if (values[0]) {
        params.province = values[0]
    }
    if (values[1]) {
        params.city = values[1]
    }
    params.uuid = uuid()
    return createAxios({
        url: isAdminApp() ? adminAreaUrl : apiAreaUrl,
        method: 'GET',
        params: params,
    })
}

/**
 * 发送短信
 */
export function sendSms(mobile: string, templateCode: string, extend: anyObj = {}) {
    return createAxios(
        {
            url: apiSendSms,
            method: 'POST',
            data: {
                mobile: mobile,
                template_code: templateCode,
                ...extend,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 发送邮件
 */
export function sendEms(email: string, event: string, extend: anyObj = {}) {
    return createAxios(
        {
            url: apiSendEms,
            method: 'POST',
            data: {
                email: email,
                event: event,
                ...extend,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 缓存清理接口
 */
export function postClearCache(type: string) {
    return createAxios(
        {
            url: clearCacheUrl,
            method: 'POST',
            data: {
                type: type,
            },
        },
        {
            showSuccessMessage: true,
        }
    )
}

/**
 * 构建命令执行窗口url
 */
export function buildTerminalUrl(commandKey: string, uuid: string, extend: string) {
    const adminInfo = useAdminInfo()
    return (
        getUrl() + terminalUrl + '?command=' + commandKey + '&uuid=' + uuid + '&extend=' + extend + '&batoken=' + adminInfo.getToken() + '&server=1'
    )
}

/**
 * 请求修改终端配置
 */
export function postChangeTerminalConfig(data: { manager?: string; port?: string }) {
    return createAxios({
        url: changeTerminalConfigUrl,
        method: 'POST',
        data: data,
    })
}

/**
 * 远程下拉框数据获取
 */
export function getSelectData(remoteUrl: string, q: string, params: anyObj = {}) {
    return createAxios({
        url: remoteUrl,
        method: 'get',
        params: {
            select: true,
            quickSearch: q,
            ...params,
        },
    })
}

export function buildCaptchaUrl() {
    return getUrl() + captchaUrl + '?server=1'
}

export function getCaptchaData(id: string, apiBaseURL: string) {
    return createAxios({
        url: apiBaseURL + clickCaptchaUrl,
        method: 'get',
        params: {
            id,
        },
    })
}

export function checkClickCaptcha(id: string, info: string, unset: boolean, apiBaseURL: string) {
    return createAxios(
        {
            url: apiBaseURL + checkClickCaptchaUrl,
            method: 'post',
            data: {
                id,
                info,
                unset,
            },
        },
        {
            showCodeMessage: false,
        }
    )
}

export function getTablePk(table: string, connection = '') {
    return createAxios({
        url: getTablePkUrl,
        method: 'get',
        params: {
            table: table,
            connection: connection,
        },
    })
}

/**
 * 获取数据表的字段
 * @param table 数据表名
 * @param clean 只要干净的字段注释（只要字段标题）
 */
export function getTableFieldList(table: string, clean = true, connection = '') {
    return createAxios({
        url: getTableFieldListUrl,
        method: 'get',
        params: {
            table: table,
            clean: clean ? 1 : 0,
            connection: connection,
        },
    })
}

export function refreshToken() {
    const adminInfo = useAdminInfo()
    const userInfo = useUserInfo()
    return createAxios({
        url: refreshTokenUrl,
        method: 'POST',
        data: {
            refreshToken: isAdminApp() ? adminInfo.getToken('refresh') : userInfo.getToken('refresh'),
        },
    })
}

/**
 * 快速生成一个控制器的 增、删、改、查、排序 接口的请求方法
 * 本 class 实例通常直接传递给 baTable 使用，开发者可重写本类的方法，亦可直接向 baTable 传递自定义的 API 请求类
 * 表格相关网络请求无需局限于本类，开发者可于 /src/api/ 目录创建自定义的接口请求函数，并于需要的地方导入使用即可
 */
export class baTableApi {
    private controllerUrl
    public actionUrl

    constructor(controllerUrl: string) {
        this.controllerUrl = controllerUrl
        this.actionUrl = new Map([
            ['index', controllerUrl + 'index'],
            ['add', controllerUrl + 'add'],
            ['edit', controllerUrl + 'edit'],
            ['del', controllerUrl + 'del'],
            ['sortable', controllerUrl + 'sortable'],
            ['export', controllerUrl + 'export'],
            ['editPost', controllerUrl + 'edit'],
        ])
    }

    /**
     * 表格查看接口的请求方法
     * @param filter 数据过滤条件
     */
    index(filter: BaTable['filter'] = {}) {
        return createAxios<TableDefaultData>({
            url: this.actionUrl.get('index'),
            method: 'get',
            params: filter,
        })
    }

    /**
     * 获取被编辑行数据
     * @param params 被编辑行主键等
     */
    add(data: anyObj) {
        return createAxios({
            url: this.actionUrl.get('add'),
            method: 'post',
            data,
        })
    }

    /**
     * 获取被编辑行数据
     * @param params 被编辑行主键等
     */
    edit(params: anyObj) {
        return createAxios({
            url: this.actionUrl.get('edit'),
            method: 'get',
            params,
        })
    }

    editPost(data: anyObj) {
        return createAxios({
            url: this.actionUrl.get('edit'),
            method: 'post',
            data,
        })
    }

    /**
     * 表格删除接口的请求方法
     * @param ids 被删除数据的主键数组
     */
    del(ids: string[]) {
        return createAxios(
            {
                url: this.actionUrl.get('del'),
                method: 'DELETE',
                params: {
                    ids,
                },
            },
            {
                showSuccessMessage: true,
            }
        )
    }

    export(data: anyObj) {
        return createAxios({
            url: this.actionUrl.get('export'),
            method: 'get',
            data,
        })
    }

    /**
     * 向指定接口 POST 数据，本方法虽然较为通用，但请不要局限于此，开发者可于 /src/api/ 目录创建自定义的接口请求函数，并于需要的地方导入使用即可
     * @param action 请求的接口，比如 add、edit
     * @param data 要 POST 的数据
     */
    postData(action: string, data: anyObj) {
        return createAxios(
            {
                url: this.actionUrl.has(action) ? this.actionUrl.get(action) : this.controllerUrl + action,
                method: 'post',
                data,
            },
            {
                showSuccessMessage: true,
            }
        )
    }

    /**
     * 表格行排序接口的请求方法
     */
    sortable(data: anyObj) {
        return createAxios({
            url: this.actionUrl.get('sortable'),
            method: 'post',
            data,
        })
    }
}
