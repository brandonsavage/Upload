<?php

class DimensionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testWidthAndHeight()
    {
        $dimensions = new \Upload\Validation\Dimensions(100, 100);
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    /**
     * @expectedException \Upload\Exception
     */
    public function testWidthDoesntMatch()
    {
        $dimensions = new \Upload\Validation\Dimensions(200, 100);
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    /**
     * @expectedException \Upload\Exception
     */
    public function testHeightDoesntMatch()
    {
        $dimensions = new \Upload\Validation\Dimensions(100, 200);
        $file = new \Upload\FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }
}