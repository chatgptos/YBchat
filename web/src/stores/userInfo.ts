import { defineStore } from 'pinia'
import { USER_INFO } from '/@/stores/constant/cacheKey'
import { UserInfo } from '/@/stores/interface'

export const useUserInfo = defineStore('userInfo', {
    state: (): UserInfo => {
        return {
            id: 0,
            username: '',
            nickname: '',
            email: '',
            mobile: '',
            avatar: '',
            gender: 0,
            birthday: '',
            money: 0,
            score: 0,
            lastlogintime: '',
            lastloginip: '',
            jointime: '',
            motto: '',
            token: '',
            refreshToken: '',
        }
    },
    actions: {
        removeToken() {
            this.token = ''
            this.refreshToken = ''
        },
        getGenderIcon() {
            let icon = { name: 'fa fa-transgender-alt', color: 'var(--color-info)' }
            switch (this.gender) {
                case 1:
                    icon = { name: 'fa fa-mars-stroke-v', color: 'var(--color-primary)' }
                    break
                case 2:
                    icon = { name: 'fa fa-mars-stroke', color: 'var(--color-danger)' }
                    break
            }
            return icon
        },
    },
    persist: {
        key: USER_INFO,
    },
})
