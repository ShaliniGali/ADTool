
const { Upload } = require("@aws-sdk/lib-storage")
const {S3Client, ListObjectsV2Command, GetObjectCommand, HeadObjectCommand, DeleteObjectCommand, CopyObjectCommand} = require("@aws-sdk/client-s3");
const s3 = new S3Client({
    credentials:{
        accessKeyId: process.env.MINIO_ACCESS_KEY,
        secretAccessKey: process.env.MINIO_SECRET_KEY,
    },
    endpoint: process.env.MINIO_ENDPOINT_URL || 'https://s3-fips.us-gov-west-1.amazonaws.com',
    s3ForcePathStyle: true, // needed with minio?
    signatureVersion: 'v4',
    maxRetries:3,
    region:"us-gov-west-1"
});
//Allows for uploading a file to S3, returns
const uploadFile = async (req, res) => {
    try {
        const path = req.query.path ? req.query.path + "/" : "";
        const key = req.files[0].originalname.replace(' ', '-')
        const file = {
            Bucket:process.env.MINIO_BUCKET_NAME,
            Key: path + key,
            Body: req.files[0].buffer
        }
        const uploadedFile = await new Upload({
            client: s3,
            params: file
        }).done()
        return res.status(201).send({ location: uploadedFile.Location })
    } catch (err) {
        return res.status(500).send({ error: err.message })
    }
}


//fetches the names of files in a bucket, optionally using a prefix to narrow down results
const listBucket = async (req, res) => {
    // Declare truncated as a flag that the while loop is based on.

 let truncated = true;
 const bucketParams = {
       Bucket: process.env.MINIO_BUCKET_NAME,
       Prefix:req.query.prefix || ""
   }
   // while loop that runs until 'response.truncated' is false.
   const results = []
   while (truncated ) {
       try {
        const command = new ListObjectsV2Command(bucketParams)
        const response = await s3.send(command);
        results.push(...response.Contents)     // Add this page to the total results
        truncated = response.IsTruncated;
     if (truncated) {
       // Assign the ContinuationToken value to bucketParams so that the next iteration starts from the correct file
       bucketParams.ContinuationToken = response.NextContinuationToken;
     }
     // At end of the list, response.truncated is false, and the function exits the while loop.
   } catch (err) {
     truncated = false;
     return res.status(502).send(err.message)
   }
 }
 const finalData = {
     count:results.length,
     data:results
 }
 res.status(200).send(finalData)
}


const getFile = async (req, res) => {
    try{
        const params = {
            Key:req.query.path,
            Bucket:process.env.MINIO_BUCKET_NAME
        }
        const command = new GetObjectCommand(params)
        const response = await s3.send(command);
        const file = await response.Body.transformToString()
        return res.status(200).send(file)

    } catch(err){
        const statusCode = err.$metadata.httpStatusCode ? err.$metadata.httpStatusCode : 500
        return res.status(statusCode).send(err.message)
    }
}

//verfies a file exists, and if it does, retrieves its metadata
//getting metadata is the fastest way to determine if a file exists
const verifyObject = async ( req , res ) => {
    try{
        const params = {
            Key:req.query.path,
            Bucket:process.env.MINIO_BUCKET_NAME
        }
        const command = new HeadObjectCommand(params);
        const response = await s3.send(command);
        res.status(200).send({
            fileExists:true,
            metadata:response
        })
    } catch(err){
        if(err.$metadata?.httpStatusCode === 404){
            return res.status(200).send({
                fileExists:false,
                metadata:null
            })
        }
        const statusCode = err.$metadata ? err.$metadata.httpStatusCode : 500
        return res.status(statusCode).send(err.message)


    }
}

const deleteObject = async( req , res ) => {
    try {
        const params = {
            Key:req.query.path,
            Bucket:process.env.MINIO_BUCKET_NAME
        }
        const command = new DeleteObjectCommand(params)
        const result = await s3.send(command);
        res.status(202).send(result);
    } catch(err){
        const statusCode = err.$metadata ? err.$metadata.httpStatusCode : 500
        return res.status(statusCode).send(err.message)
    }
}

const moveObject = async ( req, res ) => {
    try{
        const { path, newPath } = req.query
        const Bucket = process.env.MINIO_BUCKET_NAME
        const copyCommand = new CopyObjectCommand({
            Key:newPath,
            CopySource:`${Bucket}/${path}`,
            Bucket
        })
        const deleteCommand = new DeleteObjectCommand({
            Key:path,
            Bucket,
        })
        const copyData = await s3.send(copyCommand);
        const deleteData = await s3.send(deleteCommand);
        return res.status(204).send({
            deleteData,
            copyData,
        })
    } catch(err){
        const status = err.$metadata ? err.$metadata.httpStatusCode : 500
        return res.status(status).send(err.message)
    }
}
module.exports =  {
    getFile,
    listBucket,
    uploadFile,
    verifyObject,
    deleteObject,
    moveObject
}