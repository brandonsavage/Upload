<?php
class ExtensionTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidExtension()
    {
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new \Upload\Validation\Extension('txt');
        $validation->validate($file); // <-- SHOULD NOT throw exception
    }

    /**
     * @expectedException \Upload\Exception
     */
    public function testInvalidExtension()
    {
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo_wo_ext', 'foo_wo_ext');
        $validation = new \Upload\Validation\Extension('txt');
        $validation->validate($file); // <-- SHOULD throw exception
    }
}
