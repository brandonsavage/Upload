<?php
namespace Upload;

class Exception extends \RuntimeException
{
    /**
     * @var \Upload\FileInfoInterface
     */
    protected $fileInfo;

    /**
     * Constructor
     *
     * @param string                    $message  The Exception message
     * @param \Upload\FileInfoInterface $fileInfo The related file instance
     */
    public function __construct($message, \Upload\FileInfoInterface $fileInfo = null)
    {
        $this->fileInfo = $fileInfo;

        parent::__construct($message);
    }

    /**
     * Get related file
     *
     * @return \Upload\FileInfoInterface
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }
}
