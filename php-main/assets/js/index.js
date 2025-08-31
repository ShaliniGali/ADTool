require('dotenv').config()
const dns = require('node:dns');
const express = require('express');
const util = require('util');
const multer = require('multer')
const upload = multer({
    storage: multer.memoryStorage(),
    limits: {
        fileSize: 8000000 // Compliant: 8MB
    }
});
const app = express();
const port = process.env.FILE_SERVICE_PORT || 3000;
const AWS = require('@aws-sdk/client-s3');
const s3 = new AWS.S3({
    accessKeyId: process.env.MINIO_ACCESS_KEY,
    secretAccessKey: process.env.MINIO_SECRET_KEY,
    endpoint: process.env.MINIO_ENDPOINT_URL || 's3-fips.us-gov-west-1.amazonaws.com',
    s3ForcePathStyle: true, // needed with minio?
    signatureVersion: 'v4'
});
let timeData = {
    start: 0,
    fileName: "",
}
app.disable('x-powered-by');
app.use(express.urlencoded({
    extended: false
}))
const validateDNS = (req, res, next) => {
    const whitelist = process.env.WHITELIST.split(" ")
    const lookupPort = req.socket.remotePort
    dns.lookupService(req.ip, lookupPort, (err, hostname, service) => {
        if (whitelist.includes(hostname)) {
            return next()
        }
        return res.sendStatus(403)
    });
}
const uploadFile = async (req, res) => {
    try {
        const file = {
            Bucket: process.env.MINIO_BUCKET_NAME,
            Key: req.files[0].originalname.replace(' ', '-'),
            Body: req.files[0].buffer
        }
        const uploadedFile = await s3.upload(file).promise()
        return res.status(201).send({ location: uploadedFile.Location })
    } catch (err) {
        return res.status(500).send({ error: err.message })
    }
}

const getFile = (req, res) => {
    const {
        path
    } = req.query;
    const Bucket = process.env.MINIO_BUCKET_NAME;
    s3.getObject({
        Key: path,
        Bucket
    }, function (err, data) {
        if (err) {
            return res.send(err)
        }
        return res.status(200).send(data.Body)
    })
}
// 'assets/js' is the path that nginx uses to access fils. '/home/node/code/' is the directory within node.js containers
app.use('/assets', (res, req, next) => { // Put an initial middlewear to start the timer when a js file it accessed
    //writeFile(req.path);
    //timeData.startJS = new Date(); // Save initial time
    next(); // go to next middlewear
});
app.get('/assets/s3', getFile);
app.post('/assets/s3', validateDNS, upload.any(), uploadFile)
app.use('/assets', express.static('/home/node/code/dist/assets', {
    maxage: 3600
}));

// This url is used to serve all sources for the react sso tile app
app.use('/static', express.static('/home/node/code/dist/static',  {
    maxage: 3600,
}))

app.listen(port, () => {
    console.log(`Node listening on port ${port}`)
});

//Check files for Symlinks
function writeFile(d) {
    log_file.write(util.format(d) + '\n');
};

exports.getFile = getFile
exports.validateDNS = validateDNS
exports.uploadFile = uploadFile