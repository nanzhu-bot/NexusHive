import { defineStore } from 'pinia'
import { baTableApi } from '/@/api/common'

export const useMedia = defineStore('media', {
    state: (): any => {
        return {
            mediaConfig: {},
        }
    },
    actions: {
        async getMediaConfig() {
            const res = await new baTableApi('/admin/routine.Config/').index()
            this.mediaConfig = res.data
        },
    },
    getters: {
        uploadList: (state: any) => {
            if (state.mediaConfig.list) {
                return state.mediaConfig.list.upload.list
            } else {
                return []
            }
        },
        uploadApi: (state: any) => {
            return 'https://' + state.uploadList[state.uploadList.length - 1].value
        },
    },
})
