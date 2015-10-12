<?php

class TranslationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Setup (each test)
     */
    public function setUp()
    {
        $this->translation = new \Upload\Translation('pt-BR');
    }

    /********************************************************************************
    * Tests
    *******************************************************************************/

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructionWithInvalidLanguage()
    {
        $translation = new \Upload\Translation('xy');
    }

    public function testConstructionWithValidLanguage()
    {
        $this->assertInstanceOf('Upload\Translation', $this->translation);
    }

    public function testTranslatedMessageWithoutPlaceholders()
    {
        $this->assertEquals('Arquivo já existe', $this->translation->getMessage('File already exists'));
    }

    public function testTranslatedMessageWithPlaceholders()
    {
        $this->assertEquals(
            'Não foi possível encontrar o arquivo enviado com a chave: image',
            $this->translation->getMessage('Cannot find uploaded file identified by key: %s', array('image'))
         );
    }
}
