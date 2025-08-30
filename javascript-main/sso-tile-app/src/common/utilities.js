import { APP_STATUS } from './constants'

export function isAppDeployed(status) {
    return Object.values(APP_STATUS).includes(status)
}

export function getCookieByName(name) {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'))
    return match ? match[2] : null
}

export function getCSRFToken() {
    return getCookieByName('rhombus_token_cookie')
}

export function convertToFormData(params) {
    const formData = new FormData()

    for (let key in params) {
        formData.append(key, params[key])
    }

    return formData
}
