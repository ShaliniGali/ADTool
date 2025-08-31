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
        post:jest.fn()
    }
}

jest.mock('express', () => {
    mockExpress.urlencoded = jest.fn()
    mockExpress.static = jest.fn()

    return mockExpress
})
jest.mock('@aws-sdk/client-s3', () => {
    return {
        S3: class {
            getObject = function (arg,callback) {
                return callback(false, {Body:'test-image'}) 
            };
            upload = jest.fn(() => {
                return {
                    promise:jest.fn(() => "location/filename.png")
                }
            })
        }
    }
})
jest.mock('node:dns', () => {
    return {
        lookupService:function(ip,port,callback){
            callback(null,'php','ssh')
        }
    }
})
beforeAll(() => {
    require('../index')
})

test('server starts up correctly', async () => {
    expect(appListen).toHaveBeenCalledWith(3000,expect.any(Function))
})

test('server disables the correct headers', () => {
    expect(appDisable).toHaveBeenCalledWith('x-powered-by')
})

test('server returns files at /assests/s3', () => {
    const { getFile } = require('../index.js')
    expect(appGet).toHaveBeenCalledWith('/assets/s3', getFile)
})

test('dns rejects if not in whitelist', () =>{
    const { validateDNS } = require('../index.js')
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
    const { validateDNS } = require('../index.js')
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

test('calls s3 file upload and return status 201 on success', async () => {
    const { uploadFile } = require('../index.js')
    const req = {
        files:[{
            originalname:"some-image.png",
            buffer:'I am a buffer'
        }]
    }
    const res = {
        status:jest.fn(() => {
            return {
                send:jest.fn()
            }
        })
    }
    await uploadFile(req,res)
    expect(res.status).toHaveBeenCalledWith(201)
})
