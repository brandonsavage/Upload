<?php
class MimetypeTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidMimetype()
    {
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Upload\Validation\Mimetype(array('text/plain'));
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    /**
     * @expectedException \Upload\Exception
     */
    public function testInvalidMimetype()
    {
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Upload\Validation\Mimetype(array('image/png'));
        $validation->validate($file); // <-- SHOULD throw exception
    }
}
