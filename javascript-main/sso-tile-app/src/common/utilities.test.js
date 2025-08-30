const { APP_STATUS } = require('./constants')
const {
    isAppDeployed,
    convertToFormData,
    getCookieByName,
    getCSRFToken,
} = require('./utilities')

let __cookies
Object.defineProperty(window.document, 'cookie', {
    get: () => __cookies,
    set: (v) => {__cookies = v},
    split: (s) => __cookies.split(s),
})

test('isAppDeployed utility negative tests', () => {
    expect(isAppDeployed()).toBe(false)
    expect(isAppDeployed('NIPR')).toBe(false)
})

test('isAppDeployed utility positive tests', () => {
    expect(isAppDeployed(APP_STATUS.REGISTERED)).toBe(true)
    expect(isAppDeployed(APP_STATUS.NOT_REGISTERED)).toBe(true)
})

test('convertToFormData', () => {
    const testData = { foo: 'bar' }
    const formData = convertToFormData(testData)
    expect(formData.has('foo')).toBe(true)
    expect(formData.has('bar')).toBe(false)
    expect(Array.from(formData)[0][0]).toBe('foo')
    expect(Array.from(formData)[0][1]).toBe('bar')
})

test('getCookieByName', () => {
    const token = 'abcdefg12345676789'
    __cookies = `dev.rhombuspower.com:8003=1673454506701; token=${token}; foo=bar`
    expect(getCookieByName('test')).toBe(null)
    expect(getCookieByName('foo')).toBe('bar')
    expect(getCookieByName('token')).toBe(token)
})

test('getCSRFToken', () => {
    const token = 'abcdefg12345676789'
    __cookies = `dev.rhombuspower.com:8003=1673454506701; rhombus_token_cookie=${token}; foo=bar`
    expect(getCSRFToken('rhombus_token_cookie')).toBe(token)
})
