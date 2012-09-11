<?php
/**
 * Upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     1.0.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Upload;

/**
 * File
 *
 * This class provides the implementation for an uploaded file. It exposes
 * common attributes for the uploaded file (e.g. name, extension, media type)
 * and allows you to attach validations to the file that must pass for the
 * upload to succeed.
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class File extends \SplFileInfo
{
    /**
     * The file's storage delegate
     * @var \Upload\Storage\Base
     */
    protected $storage;

    /**
     * Array of validations
     * @var array[\Upload\Validation\Base]
     */
    protected $validations;

    /**
     * Array of validation errors
     * @var array
     */
    protected $errors;

    /**
     * Original file name provided by client
     * @var string
     */
    protected $originalName;

    /**
     * File name (without extension)
     * @var string
     */
    protected $name;

    /**
     * File extension (without leading dot)
     * @var string
     */
    protected $extension;

    /**
     * File mimetype (e.g. "image/png")
     * @var string
     */
    protected $mimetype;

    /**
     * Constructor
     * @param  string                        $key            The file's key in $_FILES superglobal
     * @param  \Upload\Storage\Base          $storage        The method with which to store file
     * @throws \InvalidArgumentException     If $_FILES key does not exist
     */
    public function __construct($key, \Upload\Storage\Base $storage)
    {
        if (!isset($_FILES[$key])) {
            throw new \InvalidArgumentException("Cannot find uploaded file identified by key: $key");
        }
        $this->storage = $storage;
        $this->validations = array();
        $this->errors = array();
        $this->originalName = $_FILES[$key]['name'];
        parent::__construct($_FILES[$key]['tmp_name']);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $this->name = pathinfo($this->originalName, PATHINFO_FILENAME);
        }

        return $this->name;
    }

    /**
     * Set name (without extension)
     * @param  string           $name
     * @return \Upload\File     Self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get file name with extension
     * @return string
     */
    public function getNameWithExtension()
    {
        return sprintf('%s.%s', $this->getName(), $this->getExtension());
    }

    /**
     * Get file extension (without leading dot)
     * @return string
     */
    public function getExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = pathinfo($this->originalName, PATHINFO_EXTENSION);
        }

        return $this->extension;
    }

    /**
     * Get mimetype
     * @return string
     */
    public function getMimetype()
    {
        if (!isset($this->mimeType)) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }

        return $this->mimetype;
    }

    /********************************************************************************
    * Validate
    *******************************************************************************/

    /**
     * Add file validations
     * @param \Upload\Validation\Base|array[\Upload\Validation\Base] $validations
     */
    public function addValidations($validations)
    {
        if (!is_array($validations)) {
            $validations = array($validations);
        }
        foreach ($validations as $validation) {
            if ($validation instanceof \Upload\Validation\Base) {
                $this->validations[] = $validation;
            }
        }
    }

    /**
     * Get file validations
     * @return array[\Upload\Validation\Base]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Validate file
     * @return bool True if valid, false if invalid
     */
    public function validate()
    {
        foreach ($this->validations as $validation) {
            if ($validation->validate($this) === false) {
                $this->errors[] = $validation->getMessage();
            }
        }

        return empty($this->errors);
    }

    /**
     * Get file validation errors
     * @return array[String]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Add file validation error
     * @param  string
     * @return \Upload\File Self
     */
    public function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /********************************************************************************
    * Upload
    *******************************************************************************/

    /**
     * Upload file (delegated to storage object)
     * @return bool
     * @throws \RuntimeException If file does not validate
     */
    public function upload()
    {
        if ($this->validate() === false) {
            throw new \RuntimeException('Upload failed. File does not validate.');
        }

        return $this->storage->upload($this);
    }
}
