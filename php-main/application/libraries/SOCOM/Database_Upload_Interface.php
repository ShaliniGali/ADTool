<?php

interface Database_Upload_Interface {
    public function validateFile(string $uploadFileName);

    public function virusScan();

    public function saveUpload();

    public function setFilePostName(string $val);

    public function saveToS3(string $uploadFileName, UploadType $uploadType);

    public function deleteUploadedFile(string $uploadFileName);

    public function checkResult($status);

    public function setParams(array $params, UploadType $uploadType);

    public function saveToDatabase();
}