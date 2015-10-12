<?php
use org\bovigo\vfs\vfsStream;

class FileTest extends PHPUnit_Framework_TestCase
{
    protected $assetsDirectory;

    protected $storage;

    /**
     * Virtual directory created with vfsStream used for testing
     * @var resource
     */
    protected $root;

    /********************************************************************************
    * Setup
    *******************************************************************************/

    public function setUp()
    {
        // Create virtual file directory
        $this->root = vfsStream::setup('files');

        // Prepare uploaded file
        $this->assetsDirectory = dirname(__FILE__) . '/assets';
        $_FILES['foo'] = array(
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => UPLOAD_ERR_OK
        );
    }

    public function getNewFile()
    {
        if (is_null($this->storage)) {
            // Prepare storage
            $this->storage = $this->getMock(
                '\Upload\Storage\FileSystem',
                array('upload'),
                array($this->assetsDirectory)
            );
            $this->storage->expects($this->any())
                          ->method('upload')
                          ->will($this->returnValue(true));
        }

        // Prepare file
        $file = $this->getMock(
            '\Upload\File',
            array('isUploadedFile'),
            array('foo', $this->storage)
        );
        $file->expects($this->any())
             ->method('isUploadedFile')
             ->will($this->returnValue(true));

        return $file;
    }

    public function getNewTranslation()
    {
        return new \Upload\Translation('pt-BR');
    }

    /********************************************************************************
    * Tests
    *******************************************************************************/

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructionWithInvalidKey()
    {
        $file = new \Upload\File('bar', new \Upload\Storage\FileSystem($this->assetsDirectory));
    }

    public function testGetName()
    {
        $file = $this->getNewFile();
        $this->assertEquals('foo', $file->getName());
    }

    public function testGetNameWithExtension()
    {
        $file = $this->getNewFile();
        $this->assertEquals('foo.txt', $file->getNameWithExtension());
    }

    public function testGetNameWithExtensionUsingCustomName()
    {
        $file = $this->getNewFile();
        $file->setName('bar');
        $this->assertEquals('bar.txt', $file->getNameWithExtension());
    }

    public function testGetMimetype()
    {
        $file = $this->getNewFile();
        $this->assertEquals('text/plain', $file->getMimetype());
    }

    public function testAddValidationErrors()
    {
        $file = $this->getNewFile();
        $file->addError('Error');
        $this->assertEquals(1, count($file->getErrors()));
    }

    public function testIsValidIfNoValidations()
    {
        $file = $this->getNewFile();
        $this->assertEmpty($file->getErrors());
    }

    public function testWillUploadIfNoValidations()
    {
        $file = $this->getNewFile();
        $this->assertTrue($file->upload());
    }

    public function testAddValidations()
    {
        $file = $this->getNewFile();
        $file->addValidations(new \Upload\Validation\Mimetype(array(
            'text/plain'
        )));
        $this->assertEquals(1, count($file->getValidations()));
    }

    public function testWillUploadWithPassingValidations()
    {
        $file = $this->getNewFile();
        $file->addValidations(new \Upload\Validation\Mimetype(array(
            'text/plain'
        )));
        $this->assertTrue($file->upload());
    }

    /**
     * @expectedException \Upload\Exception\UploadException
     */
    public function testWillNotUploadWithFailingValidations()
    {
        $file = $this->getNewFile();
        $file->addValidations(new \Upload\Validation\Mimetype(array(
            'image/png'
        )));
        $file->upload();
    }

    public function testPopulatesErrorsWithFailingValidations()
    {
        $file = $this->getNewFile();
        $file->addValidations(new \Upload\Validation\Mimetype(array(
            'image/png'
        )));
        try {
            $file->upload();
        } catch(\Upload\Exception\UploadException $e) {
            $this->assertEquals(1, count($file->getErrors()));
        }
    }

    public function testValidationFailsIfUploadErrorCode()
    {
        $_FILES['foo']['error'] = 4;
        $file = $this->getNewFile();
        $this->assertFalse($file->validate());
    }

    public function testValidationFailsIfNotUploadedFile()
    {
        $file = $this->getMock(
            '\Upload\File',
            array('isUploadedFile'),
            array('foo', new \Upload\Storage\FileSystem($this->assetsDirectory))
        );
        $file->expects($this->any())
             ->method('isUploadedFile')
             ->will($this->returnValue(false));
        $this->assertFalse($file->validate());
    }

    public function testParsesHumanFriendlyFileSizes()
    {
        $this->assertEquals(100, \Upload\File::humanReadableToBytes('100'));
        $this->assertEquals(102400, \Upload\File::humanReadableToBytes('100K'));
        $this->assertEquals(104857600, \Upload\File::humanReadableToBytes('100M'));
        $this->assertEquals(107374182400, \Upload\File::humanReadableToBytes('100G'));
        $this->assertEquals(100, \Upload\File::humanReadableToBytes('100F')); // <-- Unrecognized. Assume bytes.
    }

    public function testErrorCodeUsingTranslation()
    {
        $_FILES['foo']['error'] = 4;

        // Prepare storage
        $this->storage = $this->getMock(
            '\Upload\Storage\FileSystem',
            array('upload'),
            array($this->assetsDirectory)
        );
        $this->storage->expects($this->any())
                      ->method('upload')
                      ->will($this->returnValue(true));

        // Prepare file
        $file = $this->getMock(
            '\Upload\File',
            array('isUploadedFile'),
            array('foo', $this->storage, $this->getNewTranslation())
        );
        $file->expects($this->any())
             ->method('isUploadedFile')
             ->will($this->returnValue(true));

        $this->assertFalse($file->validate());
        $this->assertCount(1, $file->getErrors());
        $this->assertContains('Nenhum arquivo enviado', $file->getErrors());
    }
}
