import createAxios from '/@/utils/axios'
export function getXmList() {
    return createAxios({
        url: 'admin/Project/index',
        method: 'get',
    })
}
export function addXm(data: any) {
    return createAxios({
        url: 'admin/Project/add',
        method: 'post',
        data: data,
    })
}

export function getSbList() {
    return createAxios({
        url: 'admin/Device/index',
        method: 'get',
    })
}
export function getFzList() {
    return createAxios({
        url: 'admin/Aircraftload/index',
        method: 'get',
    })
}

export function addHx(data: any) {
    return createAxios({
        url: 'admin/Airline/add',
        method: 'post',
        data: data,
    })
}

export function editHx(data: any) {
    return createAxios({
        url: 'admin/Airline/edit',
        method: 'post',
        data: data,
    })
}

export function deleteHx(data: any) {
    return createAxios({
        url: 'admin/Airline/del',
        method: 'post',
        data: data,
    })
}

export function getHxDetail(id: any) {
    return createAxios({
        url: 'admin/Airline/edit',
        method: 'get',
        params: {
            id: id,
        },
    })
}

export function getHxList(data: any) {
    return createAxios({
        url: 'admin/Airline/index',
        method: 'get',
        params: data,
    })
}

export function getCeshi() {
    return createAxios({
        url: 'admin/Index/acc',
        method: 'get',
    })
}
