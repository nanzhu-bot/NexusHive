import { useBaAccount } from '/@/stores/baAccount'
import { useSiteConfig } from '/@/stores/siteConfig'
import createAxios from '/@/utils/axios'

export const url = '/admin/crud.Crud/'

export function generate(data: anyObj) {
    return createAxios(
        {
            url: url + 'generate',
            method: 'post',
            data: data,
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function getFileData(table: string, commonModel = 0) {
    return createAxios({
        url: url + 'getFileData',
        method: 'get',
        params: {
            table: table,
            commonModel: commonModel,
        },
    })
}

export function generateCheck(data: anyObj) {
    return createAxios(
        {
            url: url + 'generateCheck',
            method: 'post',
            data: data,
        },
        {
            showCodeMessage: false,
        }
    )
}

export function parseFieldData(data: anyObj) {
    return createAxios({
        url: url + 'parseFieldData',
        method: 'post',
        data: data,
    })
}

export function postLogStart(id: string, type: string) {
    const data: anyObj = {
        id,
        type,
    }

    if (type == 'Cloud history') {
        const baAccount = useBaAccount()
        data['token'] = baAccount.getToken('auth')
    }

    return createAxios({
        url: url + 'logStart',
        method: 'post',
        data: data,
    })
}

export function postDel(id: number) {
    return createAxios({
        url: url + 'delete',
        method: 'post',
        data: {
            id: id,
        },
    })
}

export function checkCrudLog(table: string, connection: string) {
    return createAxios({
        url: url + 'checkCrudLog',
        method: 'get',
        params: {
            table: table,
            connection: connection,
        },
    })
}

export function uploadLog(data: anyObj) {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    data['ba-user-token'] = baAccount.getToken('auth')
    return createAxios({
        url: siteConfig.apiUrl + '/api/v6.Crud/uploadLog',
        data: data,
        method: 'post',
    })
}

export function uploadCompleted(data: anyObj) {
    return createAxios({
        url: url + 'uploadCompleted',
        data: data,
        method: 'post',
    })
}

export function logs(data: anyObj = {}) {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    data['ba-user-token'] = baAccount.getToken('auth')
    return createAxios({
        url: siteConfig.apiUrl + '/api/v6.Crud/logs',
        data: data,
        method: 'post',
    })
}

export function delLog(data: anyObj = {}) {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    data['ba-user-token'] = baAccount.getToken('auth')
    return createAxios({
        url: siteConfig.apiUrl + '/api/v6.Crud/del',
        data: data,
        method: 'post',
    })
}
