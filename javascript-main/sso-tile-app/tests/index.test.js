/**
 * @jest-environment node
 */
const appListen = jest.fn((arg, callback) => {
    callback()
})
const appDisable = jest.fn()
let requestIsFailure = false;
const appGet =  jest.fn((path, callback) => {
    const res = {
        send: jest.fn((arg) => {
            return arg
        }),
        status:function(){
            return this
        },
    }
    const req = {
        query: {
            path: 'test-aws/021522_Hub.pdf',

        }
    }
    callback(req, res)
})
 function mockExpress(){
    return {
        
        disable: appDisable,
        listen: appListen,
        use: jest.fn((path, arg) => {
            if(typeof arg == 'function'){
                arg(jest.fn(), jest.fn(), jest.fn())
            }
        }),
        get: appGet,
        post:jest.fn(),
        put:jest.fn(),
        delete:jest.fn(),


    }
}

jest.mock('express', () => {
    mockExpress.urlencoded = jest.fn()
    mockExpress.static = jest.fn()

    return mockExpress
})

jest.mock('node:dns', () => {
    return {
        lookupService:function(ip,port,callback){
            callback(null,'php','ssh')
        }
    }
})

beforeAll(() => {
    require('../../index')
})

test('server starts up correctly', async () => {
    expect(appListen).toHaveBeenCalledWith(3000,expect.any(Function))
})

test('server disables the correct headers', () => {
    expect(appDisable).toHaveBeenCalledWith('x-powered-by')
})

test('server returns files at /s3/get', () => {
    const { getFile } = require('../../controllers/s3Controller')
    expect(appGet).toHaveBeenCalledWith('/s3/get', getFile)
})

test('dns rejects if not in whitelist', () =>{
    const { validateDNS } = require('../../index.js')
    process.env.WHITELIST = "localhost"
    const req = {
        socket:{
            remotePost:5000
        }
    }
    const res = {
        sendStatus:jest.fn()
    }
    const next = jest.fn()
    validateDNS(req,res,next)
    expect(res.sendStatus).toHaveBeenCalledWith(403)
})
test('dns calls next if in whitelist', () =>{
    const { validateDNS } = require('../../index.js')
    process.env.WHITELIST = "php"
    const req = {
        socket:{
            remotePost:5000
        }
    }
    const res = {
        sendStatus:jest.fn()
    }
    const next = jest.fn()
    validateDNS(req,res,next)
    expect(next).toHaveBeenCalled()
})


