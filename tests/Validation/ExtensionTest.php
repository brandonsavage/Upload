<?php
class ExtensionTest extends PHPUnit_Framework_TestCase
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

        $this->translation = new \Upload\Translation('pt-BR');

        // Reset $_FILES superglobal
        $_FILES['foo'] = array(
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => 0
        );
    }

    public function testValidExtension()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\Extension('txt');
        $this->assertTrue($validation->validate($file));
    }

    public function testInvalidExtension()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\Extension('csv');
        $this->assertFalse($validation->validate($file));
    }

    public function testValidExtensionUsingTranslation()
    {
        $file = new \Upload\File('foo', $this->storage);
        $validation = new \Upload\Validation\Extension('txt', $this->translation);
        $this->assertTrue($validation->validate($file));
    }
}
