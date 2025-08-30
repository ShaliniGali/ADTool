/**
 * @jest-environment node
 */

const s3Controller = require('../controllers/s3Controller')
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
jest.mock('@aws-sdk/lib-storage', () => {
    return {
        Upload:class{
            done = jest.fn()
        }
    }
})
class MockError extends Error{
    constructor(message, statusCode){
        super(message)
        this.$metadata = {
            httpStatusCode:statusCode
        }
    }
}
jest.mock('@aws-sdk/client-s3', () => {
    return {
        S3Client: class {
            constructor(){
                this.IsTruncated = false
                this.send = async (params) => {
                    if(params.input?.Prefix == "testerror" ||params.input?.Key == "testerror" || params.input?.Key == "error404" ||params.input?.Key == "error500"){
                        if(params.input?.Key == "error404"){
                            throw new MockError("Test error", 404)
                        } else {
                            throw new MockError("Test error", 500)
                        }
                    } 
                    this.IsTruncated = !this.IsTruncated
                    return {
                        Contents: [1,2,3], 
                        Body:{
                            transformToString:jest.fn()
                            },
                        IsTruncated:this.IsTruncated,
                        NextContinuationToken:"abcd"
                        
                    }
                }
            }
        },
        ListObjectsV2Command: class {
            constructor(input){
                this.input = input
            }
        },
        GetObjectCommand: class {
            constructor(input){
                this.input = input
            }
        },
        HeadObjectCommand: class {
            constructor(input){
                this.input = input
            }
        },
        DeleteObjectCommand: class {
            constructor(input){
                this.input = input
            }
        },
        CopyObjectCommand: class {},
    }
})

describe('uploadFile', () => {
    const { uploadFile } = s3Controller;
    const req = {
        files:[{
            originalname:"some-image.png",
            buffer:'I am a buffer'
        }],
        query:{
            path:"/some/path"
        }
    }
    it('should return a 201 on upload', async () => {
        const res = {
            status:jest.fn(() => {
                return {
                    send:jest.fn()
                }
            })
        };
        await uploadFile(req,res)
        expect(res.status).toHaveBeenCalledWith(201)
    })
})

describe('listBucket', () => {
    it('should respond with data and a count of the data returned', async () => {
        const req = {
            query:{
                prefix:"test"
            }
        }
        const res = {
            status:jest.fn(() => {
                return {
                    send:jest.fn()
                }
            })
        }
        await s3Controller.listBucket(req,res)
        expect(res.status).toBeCalledWith(200)
    })
    it('should respond with a 502 in case of error', async () => {
        const req = {
            query:{
                prefix:"testerror"
            }
        }
        const res = {
            status:jest.fn(() => {
                return {
                    send:jest.fn()
                }
            })
        }
        await s3Controller.listBucket(req,res)
        expect(res.status).toBeCalledWith(502)
    })
})

describe('verifyObject', () => {
    it('should respond with data and a count of the data returned', async() => {
        const req = {
            query:{
                path:"test"
            }
        }
        const res = {
            status:jest.fn(() => {
                return {
                    send:jest.fn()
                }
            })
        }
        await s3Controller.verifyObject(req,res)
        expect(res.status).toBeCalledWith(200)
    })
    it('should respond with a 200 if the file does not exist', async () => {
        const req = {
            query:{
                path:"error404"
            }
        }
        const send = jest.fn()
        const res ={
            status:jest.fn(() => {
                return {
                    send:send
                }
            })
        };
       await s3Controller.verifyObject(req,res)
        expect(res.status).toBeCalledWith(200)
        expect(send).toBeCalledWith({fileExists:false, metadata:null})

    })
    it('should respond with the error code if its not 404', async () => {
        const req = {
            query:{
                path:"error500"
            }
        }
        const send = jest.fn()
        const res ={
            status:jest.fn(() => {
                return {
                    send:send
                }
            })
        };
        await s3Controller.verifyObject(req,res)
        expect(res.status).toBeCalledWith(500)

    })
})

    describe('getFile', () => {
        it('should return a status 200 along with the data', async () => {
            const req = {
                query:{
                    path:"test"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.getFile(req,res)
            expect(res.status).toHaveBeenCalledWith(200)
        })
        it('should return an error on failure', async () => {
            const req = {
                query:{
                    path:"testerror"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.getFile(req,res)
            expect(res.status).toHaveBeenCalledWith(500)
        })
    })
    describe('deleteObject', () => {
        it('should return a status 200 along with the data', async () => {
            const req = {
                query:{
                    path:"test"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.deleteObject(req,res)
            expect(res.status).toHaveBeenCalledWith(202)
        })
        it('should return an error code matching the AWS error', async () => {
            const req = {
                query:{
                    path:"error500"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.deleteObject(req,res)
            expect(res.status).toHaveBeenCalledWith(500)
        })
    })
    describe('moveObject', () => {
        it('should return a status 200 along with the data', async () => {
            const req = {
                query:{
                    path:"test"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.moveObject(req,res)
            expect(res.status).toHaveBeenCalledWith(204)
        })
        it('should return a an error code matching the AWS error', async () => {
            const req = {
                query:{
                    path:"error404"
                }
            }
            const res = {
                status:jest.fn(() => {
                    return {
                        send:jest.fn()
                    }
                })
            }
            await s3Controller.moveObject(req,res)
            expect(res.status).toHaveBeenCalledWith(404)
        })
    })

