<?php
class FileSizeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp()
    {
        // Path to test assets
        $this->assetsDirectory = dirname(__DIR__) . '/assets';

        // Create stubbed storage instance
        $this->storage = $this->getMock(
            '\Upload\Storage\FileSystem',
            array('upload'),
            array($this->assetsDirectory)
        );
        $this->storage->expects($this->any())
                      ->method('upload')
                      ->will($this->returnValue(true));

        // Reset $_FILES superglobal
        $_FILES['foo'] = array(
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt'
        );
    }

    public function testValidFileSize()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\FileSize(500);
        $this->assertTrue($validation->validate($file));
    }

    public function testValidFileSizeWithHumanReadableArgument()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\FileSize('500B');
        $this->assertTrue($validation->validate($file));
    }

    public function testInvalidFileSize()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\FileSize(400);
        $this->assertFalse($validation->validate($file));
    }

    public function testInvalidFileSizeWithHumanReadableArgument()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\FileSize('400B');
        $this->assertFalse($validation->validate($file));
    }
}
