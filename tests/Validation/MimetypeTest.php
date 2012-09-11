<?php
class MimetypeTest extends PHPUnit_Framework_TestCase
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
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => 0
        );
    }

    public function testValidMimetype()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\Mimetype(array(
            'text/plain'
        ));
        $this->assertTrue($validation->validate($file));
    }

    public function testInvalidMimetype()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\Mimetype(array(
            'image/png'
        ));
        $this->assertFalse($validation->validate($file));
    }
}
