import type { AxiosRequestConfig } from 'axios'
import type { UploadRawFile } from 'element-plus'
import { sha1 } from 'js-sha1'
import { useSiteConfig } from '/@/stores/siteConfig'
import createAxios from '/@/utils/axios'
import { fullUrl, isAdminApp } from '/@/utils/common'
import { randomNum, shortUuid } from '/@/utils/random'

export const state = () => {
    const siteConfig = useSiteConfig()
    return siteConfig.upload.mode == 'local' ? 'disable' : 'enable'
}

export async function fileUpload(fd: FormData, params: anyObj = {}, config: AxiosRequestConfig = {}) {
    const siteConfig = useSiteConfig()
    const file = fd.get('file') as UploadRawFile
    const sha1 = await getFileSha1(file)
    const fileKey = getSaveName(file, sha1)
    fd.append('key', fileKey)
    for (const key in siteConfig.upload.params) {
        fd.append(key, siteConfig.upload.params[key])
    }

    // 接口要求file排在最后
    fd.delete('file')
    fd.append('file', file)

    return new Promise((resolve, reject) => {
        createAxios({
            url: siteConfig.upload.url,
            method: 'POST',
            data: fd,
            params: params,
            timeout: 0,
            ...config,
        })
            .then(() => {
                const fileUrl = '/' + fileKey
                createAxios({
                    url: isAdminApp() ? '/admin/Alioss/callback' : '/api/Alioss/callback',
                    method: 'POST',
                    data: {
                        url: fileUrl,
                        name: file.name,
                        size: file.size,
                        type: file.type,
                        sha1: sha1,
                    },
                })
                resolve({
                    code: 1,
                    data: {
                        file: {
                            full_url: fullUrl(fileUrl),
                            url: fileUrl,
                        },
                    },
                    msg: '',
                    time: Date.now(),
                })
            })
            .catch((res) => {
                reject({
                    code: 0,
                    data: res,
                    msg: res.message,
                    time: Date.now(),
                })
            })
    }) as ApiPromise
}

export function getSaveName(file: UploadRawFile, sha1: string) {
    const fileSuffix = file.name.substring(file.name.lastIndexOf('.') + 1)
    const fileName = file.name.substring(0, file.name.lastIndexOf('.'))
    const dateObj = new Date()

    const replaceArr: anyObj = {
        '{topic}': 'default',
        '{year}': dateObj.getFullYear(),
        '{mon}': ('0' + (dateObj.getMonth() + 1)).slice(-2),
        '{day}': ('0' + dateObj.getDate()).slice(-2),
        '{hour}': dateObj.getHours(),
        '{min}': dateObj.getMinutes(),
        '{sec}': dateObj.getSeconds(),
        '{random}': shortUuid(),
        '{random32}': randomNum(32, 32),
        '{fileName}': fileName.substring(0, 15),
        '{suffix}': fileSuffix,
        '{.suffix}': '.' + fileSuffix,
        '{fileSha1}': sha1,
    }
    const replaceKeys = Object.keys(replaceArr).join('|')
    const siteConfig = useSiteConfig()

    const saveName = siteConfig.upload.saveName[0] == '/' ? siteConfig.upload.saveName.slice(1) : siteConfig.upload.saveName

    return saveName
        .replace(new RegExp(replaceKeys, 'gm'), (match: string) => {
            return replaceArr[match]
        })
        .replace(/[\s:@#?&=',+]+/gu, '')
}

async function getFileSha1(file: UploadRawFile): Promise<string> {
    const hash = sha1.create()
    const chunkSize = 50 * 1024 * 1024

    for (let index = 0; index < file.size; index += chunkSize) {
        await hashBlob(file.slice(index, index + chunkSize))
    }

    function hashBlob(blob: Blob): Promise<void> {
        return new Promise((resolve, reject) => {
            const reader = new FileReader()
            reader.onload = ({ target }) => {
                if (target && target.result) {
                    hash.update(target.result as ArrayBuffer)
                    resolve()
                } else {
                    reject(new Error('文件读取失败！'))
                }
            }
            reader.onerror = (e) => {
                reject(e)
            }
            reader.readAsArrayBuffer(blob)
        })
    }

    return hash.hex()
}
