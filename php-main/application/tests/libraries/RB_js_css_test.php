<?php
class RB_js_css_test extends TestCase 
{
    public function setUp(): void
    {
        parent::setUp();
        // Get object to test
        $this->obj = new RB_js_css();

        MonkeyPatch::patchFunction('file_exists', true, RB_js_css::class);
        MonkeyPatch::patchFunction('fopen', 'test_file', RB_js_css::class);
        MonkeyPatch::patchFunction('unlink', 'true', RB_js_css::class);
    }

    public function test_compress_cache_false_p1() {
        $files = [
            ['test.css', 'global'],
            ['test.css', 'custom'],
            ['test.css', 'error']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_cache_false_sipr() {
        MonkeyPatch::patchConstant(
            'P1_FLAG',
            FALSE,
            RB_js_css::class . '::compress'
        );
        MonkeyPatch::patchConstant(
            'UI_SIPR_ENVIRONMENT',
            TRUE,
            RB_js_css::class . '::compress'
        );

        $files = [
            ['test.css', 'global']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_cache_false_other() {
        MonkeyPatch::patchConstant(
            'P1_FLAG',
            FALSE,
            RB_js_css::class . '::compress'
        );

        $files = [
            ['test.css', 'global']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_cache_p1() {
        MonkeyPatch::patchConstant(
            'FILE_CACHING_CSS_JS',
            TRUE,
            RB_js_css::class . '::compress'
        );

        $files = [
            ['test.js', 'global'],
            ['test.js', 'custom'],
            ['test.js', 'error']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_cache_sipr() {
        MonkeyPatch::patchConstant(
            'FILE_CACHING_CSS_JS',
            TRUE,
            RB_js_css::class . '::compress'
        );
        MonkeyPatch::patchConstant(
            'P1_FLAG',
            FALSE,
            RB_js_css::class . '::compress'
        );
        MonkeyPatch::patchConstant(
            'UI_SIPR_ENVIRONMENT',
            TRUE,
            RB_js_css::class . '::compress'
        );

        $files = [
            ['test.js', 'global']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_cache_other() {
        MonkeyPatch::patchConstant(
            'FILE_CACHING_CSS_JS',
            TRUE,
            RB_js_css::class . '::compress'
        );
        MonkeyPatch::patchConstant(
            'P1_FLAG',
            FALSE,
            RB_js_css::class . '::compress'
        );
        MonkeyPatch::patchConstant(
            'FILE_DOWNLOAD_CSS_JS',
            TRUE,
            RB_js_css::class . '::download_delete_file'
        );

        $files = [
            ['test.js', 'global']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_wrong_file_type() {
        $files = [
            ['test.cs', 'global'],
            ['test.css', 'custom'],
            ['test.js', 'custom']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }

    public function test_compress_mized_types() {
        $files = [
            ['test.css', 'custom'],
            ['test.js', 'custom']
        ];
        $actual = $this->obj->compress($files);
        $this->assertNull($actual);
    }
}