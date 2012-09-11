<?php
class FileSystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp()
    {
        // Path to test assets
        $this->assetsDirectory = dirname(__DIR__) . '/assets';

        // Reset $_FILES superglobal
        $_FILES['foo'] = array(
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => 0
        );
    }

    public function testInstantiationWithValidDirectory()
    {
        try {
            $storage = $this->getMock(
                '\Upload\Storage\FileSystem',
                array('upload'),
                array($this->assetsDirectory)
            );
        } catch(\InvalidArgumentException $e) {
            $this->fail('Unexpected argument thrown during instantiation with valid directory');
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInstantiationWithInvalidDirectory()
    {
        $storage = $this->getMock(
            '\Upload\Storage\FileSystem',
            array('upload'),
            array('/foo')
        );
    }

    /**
     * Test won't overwrite existing file
     * @expectedException \RuntimeException
     */
    public function testWillNotOverwriteFile()
    {
        $storage = new \Upload\Storage\FileSystem($this->assetsDirectory, false);
        $file = new \Upload\File('foo', $storage);
        $file->upload();
    }

    /**
     * Test will overwrite existing file
     */
    public function testWillOverwriteFile()
    {
        $storage = $this->getMock(
            '\Upload\Storage\FileSystem',
            array('moveUploadedFile'),
            array($this->assetsDirectory, true)
        );
        $storage->expects($this->any())
                ->method('moveUploadedFile')
                ->will($this->returnValue(true));
        $file = $this->getMock(
            '\Upload\File',
            array('isUploadedFile'),
            array('foo', $storage)
        );
        $file->expects($this->any())
             ->method('isUploadedFile')
             ->will($this->returnValue(true));
        $this->assertTrue($file->upload());
    }
}
