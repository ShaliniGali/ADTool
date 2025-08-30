<?php 
// load mock S3Client before Files_model does

use Kenjis\MonkeyPatch\Exception\ExitException;

require_once(APPPATH . 'tests/mocks/libraries/S3Client.php');

/**
* @group strategic
* @group base
* @group model
*/
class AWS_with_mocks_test extends RhombusModelTestCase 
{
    public function setUp(): void
    {
        parent::setUp();

        // Get object to test
        $this->obj = new AWS();
        
        $this->obj->session = $this->getSessionMock();
        $this->obj->DBs->KNOWLEDGE_GRAPH_UI = $this->getMethodChainingDBMock();
    }

    public function test_getConfig() {
        MonkeyPatch::patchConstant(
            'S3_ENDPOINT',
            'endpoint',
            AWS::class . '::getConfig'
        );

        $actual = $this->obj->getConfig('region', 'accessKey', 'secretKey');
        $expected = [
            'version' => 'latest',
            'region' => 'region',
            'http' => ['verify' => false],
            'credentials' => [
                'key'    => 'accessKey',
                'secret' => 'secretKey'
            ],
            'endpoint' => 'endpoint',
            'use_path_style_endpoint' => true
        ];
        $this->assertIsArray($actual);
        $this->assertEquals($expected, $actual);
    }

    public function test_deleteS3File() {
        MonkeyPatch::patchMethod(
            'AWS',
            [
                'getClient' => new awsClient()
            ]
        );

        $actual = $this->obj->deleteS3File('fileName', 'bucketName', 'region');
        $this->assertTrue($actual);
    }

    public function test_deleteS3File_error() {
        $this->expectException("Exception");
        $this->obj->deleteS3File(null, 'bucket', 'region');
    }

    public function test_save3File_error() {
        $this->expectException("Exception");
        $this->obj->saveS3File('file', 'file', null, 'region');
    }

    public function test_saveS3File() {
        MonkeyPatch::patchMethod(
            'AWS',
            [
                'getClient' => new awsClient()
            ]
        );

        $actual = $this->obj->saveS3File('fileFullPath', 'fileName', 'bucketName', 'region');
        $this->assertEquals('object/url', $actual);
    }

    // public function test_getS3File() {
    //     $op = 'path/to/myfile.pdf';
    //     MonkeyPatch::patchFunction('getContents', $op, 'AWS::getS3File');
    //     $actual = $this->obj->getS3File('path/to/', 'bucket_name', FALSE);
    //     // $expected = ["data" => "dGVzdA==", "extension" => "html"];
    //     $this->assertIsArray($actual);
    // }

    public function test_getS3File_throwsException() {
        $actual = $this->obj->getS3File(NULL, NULL, FALSE);
        $this->assertEquals(NULL, $actual);
    }

    public function test_getS3File_htm() {
        $actual = $this->obj->getS3File('test.htm', NULL, FALSE);
        $this->assertEquals(NULL, $actual);
    }

    public function test_getS3File_html() {
        $actual = $this->obj->getS3File('test.html', NULL, FALSE);
        $this->assertEquals(NULL, $actual);
    } 

    public function test_getS3File_pdf() {
        $actual = $this->obj->getS3File('test.pdf', NULL, FALSE);
        $this->assertEquals(NULL, $actual);
    }

    public function test_getS3File_empty() {
        $actual = $this->obj->getS3File('', NULL, FALSE);
        $this->assertEquals(NULL, $actual);
    }

    public function test_endsWith_empty() {
        $actual = $this->obj->endsWith('','');
        $this->assertEquals(TRUE, $actual);
    }
}

class awsClient {
    function deleteObject() {
        return true;
    }
    function getObject() {
        return ['Body' => new awsBody()];
    }
    function putObject() {
        return ['ObjectURL' => '/bucketName/object/url'];
    }
}
class awsBody {
    function getContents() {
        return 'contents';
    }
}