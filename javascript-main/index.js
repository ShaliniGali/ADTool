const dns = require('node:dns');
const express = require('express');
const helmet = require('helmet');
const port = process.env.FILE_SERVICE_PORT || 3000

const multer = require('multer')
const app = express();
const s3Controller = require('./controllers/s3Controller')
const upload = multer({
    storage: multer.memoryStorage(),
    limits: {
        fileSize: 10000000 // Compliant: 10MB
    }
});

const validateDNS = (req, res, next) => {
    const whitelist = process.env.WHITELIST?.split(" ")
    const lookupPort = req.socket.remotePort
    dns.lookupService(req.ip, lookupPort, (err, hostname, service) => {
        if (whitelist.includes(hostname)) {
            return next()
        }
        return res.sendStatus(403)
    });
}




// 'assets/js' is the path that nginx uses to access fils. '/home/node/code/' is the directory within node.js containers
app.disable('x-powered-by');
app.use(helmet());

app.use(express.urlencoded({
    extended: false
}))
app.get('/s3/get', s3Controller.getFile);
app.get('/s3/list', s3Controller.listBucket);
app.get('/s3/verify', s3Controller.verifyObject);
app.delete('/s3/delete', s3Controller.deleteObject);
app.put('/s3/move', s3Controller.moveObject);

app.post('/s3/upload', validateDNS, upload.any(), s3Controller.uploadFile)
app.use('/assets', express.static('/home/node/code/dist/assets', {
    maxage: 3600
}));
app.use('/static', express.static('/home/node/code/dist/static',  {
        maxage: 3600,
    })
)

// Serve React app
app.use('/', express.static('/home/node/code/dist', {
    maxage: 3600
}));

// Handle React routing, return all requests to React app
app.get('*', (req, res) => {
    res.sendFile('/home/node/code/dist/index.html');
});

app.listen(port, (err) => {
    if(err) return console.log("Error in server setup: ", err.message)
    console.log(`Node listening on port ${port}`)
});

module.exports = {
    validateDNS
}