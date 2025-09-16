import { useAdminInfo } from '/@/stores/adminInfo'
import { useBaAccount } from '/@/stores/baAccount'
import { useSiteConfig } from '/@/stores/siteConfig'
import createAxios from '/@/utils/axios'

export const url = '/admin/Index/'

export function index() {
    return createAxios({
        url: url + 'index',
        method: 'get',
    })
}

export function login(method: 'get' | 'post', params: object = {}) {
    return createAxios({
        url: url + 'login',
        data: params,
        method: method,
    })
}

export function logout() {
    const adminInfo = useAdminInfo()
    return createAxios({
        url: url + 'logout',
        method: 'POST',
        data: {
            refreshToken: adminInfo.getToken('refresh'),
        },
    })
}

export function baAccountCheckIn(params: object = {}) {
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + '/api/user/checkIn',
            data: params,
            method: 'post',
        },
        {
            showSuccessMessage: true,
        }
    )
}

export function baAccountGetUserInfo() {
    const baAccount = useBaAccount()
    const siteConfig = useSiteConfig()
    return createAxios(
        {
            url: siteConfig.apiUrl + '/api/user/info',
            method: 'get',
        },
        {
            anotherToken: baAccount.getToken('auth'),
        }
    )
}

export function baAccountLogout() {
    const siteConfig = useSiteConfig()
    const baAccount = useBaAccount()
    return createAxios({
        url: siteConfig.apiUrl + '/api/user/logout',
        method: 'POST',
        data: {
            refreshToken: baAccount.getToken('refresh'),
        },
    })
}
