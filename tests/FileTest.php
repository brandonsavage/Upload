<?php
class FileTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp()
    {
        // Path to test assets
        $this->assetsDirectory = dirname(__FILE__) . '/assets';

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

    /**
     * Test get file name
     */
    public function testGetFileName()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals('foo', $file->getName());
    }

    /**
     * Test get file name with extension
     */
    public function testGetFileNameWithExtension()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals('foo.txt', $file->getNameWithExtension());
    }

    /**
     * Test get custom file name with extension
     */
    public function testGetCustomFileNameWithExtension()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->setName('bar');
        $this->assertEquals('bar.txt', $file->getNameWithExtension());
    }

    /**
     * Test get file extension
     */
    public function testGetFileExtension()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals('txt', $file->getExtension());
    }

    /**
     * Test get file media type
     */
    public function testGetFileMediaType()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals('text/plain', $file->getMediaType());
    }

    /**
     * Test get file size
     */
    public function testGetFileSize()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals(447, $file->getSize());
    }

    /**
     * Test get temporary file name
     */
    public function testGetTemporaryFilename()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEquals($this->assetsDirectory . '/foo.txt', $file->getTemporaryFilename());
    }

    /**
     * Test key must exist at instantiation
     * @expectedException \InvalidArgumentException
     */
    public function testKeyMustExist()
    {
        $file = new \Upload\File('bar', $this->storage); // <-- Does not exist in $_FILES superglobal
    }

    /**
     * Test set and get errors
     */
    public function testValidationErrors()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->addError('Error');
        $this->assertEquals(1, count($file->getErrors()));
    }

    /**
     * Assert validity if no validations
     */
    public function testIsValidIfNoValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertEmpty($file->getErrors());
    }

    /**
     * Test will upload successfully if no validations
     */
    public function testWillUploadIfNoValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $this->assertTrue($file->upload());
    }

    /**
     * Test add validations
     */
    public function testAddValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->addValidations(new \Upload\Validation\MediaType(array(
            'text/plain'
        )));
        $this->assertEquals(1, count($file->getValidations()));
    }

    /**
     * Test will upload with passing validations
     */
    public function testWillUploadWithPassingValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->addValidations(new \Upload\Validation\MediaType(array(
            'text/plain'
        )));
        $this->assertTrue($file->upload());
    }

    /**
     * Test will not upload with failing validations
     * @expectedException \RuntimeException
     */
    public function testWillNotUploadWithFailingValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->addValidations(new \Upload\Validation\MediaType(array(
            'image/png'
        )));
        $file->upload();
    }

    /**
     * Test populates errors with failing validations
     */
    public function testPopulatesErrorsWithFailingValidations()
    {
        $file = new \Upload\File('foo', $this->storage);
        $file->addValidations(new \Upload\Validation\MediaType(array(
            'image/png'
        )));
        try {
            $file->upload();
        } catch(\RuntimeException $e) {
            $this->assertEquals(1, count($file->getErrors()));
        }
    }
}
