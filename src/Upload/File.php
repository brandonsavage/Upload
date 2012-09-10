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
class File
{
    /**
     * The file's key in the $_FILES superglobal
     * @var string
     */
    protected $key;

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
     * File media type (e.g. "image/png")
     * @var string
     */
    protected $mediaType;

    /**
     * File size
     * @var int
     */
    protected $size;

    /**
     * Constructor
     * @param string                        $key            The file's key in $_FILES superglobal
     * @param \Upload\Storage\Base          $storage        The method with which to store file
     * @throws \InvalidArgumentException    If $_FILES key does not exist
     */
    public function __construct($key, \Upload\Storage\Base $storage)
    {
        if (!isset($_FILES[$key])) {
            throw new \InvalidArgumentException("Cannot find uploaded file identified by key: $key");
        }
        $this->key = $key;
        $this->storage = $storage;
        $this->validations = array();
        $this->errors = array();
    }

    /**
     * Get file name
     * @return string
     */
    public function getName()
    {
        if (!isset($this->name)) {
            $this->name = pathinfo($_FILES[$this->key]['name'], PATHINFO_FILENAME);
        }

        return $this->name;
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
     * Set file name (excluding extension)
     * @param  string           $name
     * @return \Upload\File     Self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get file extension (excluding leading dot)
     * @return string
     */
    public function getExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = pathinfo($_FILES[$this->key]['name'], PATHINFO_EXTENSION);
        }

        return $this->extension;
    }

    /**
     * Get file media type
     * @return string
     */
    public function getMediaType()
    {
        if (!isset($this->contentType)) {
            $finfo = new \finfo(FILEINFO_MIME);
            $contentType = $finfo->file($_FILES[$this->key]['tmp_name']);
            $contentTypeParts = preg_split('/\s*[;,]\s*/', $contentType);
            $this->mediaType = strtolower($contentTypeParts[0]);
            unset($finfo);
        }

        return $this->mediaType;
    }

    /**
     * Get file size (bytes)
     * @return int
     */
    public function getSize()
    {
        if (!isset($this->size)) {
            $this->size = filesize($_FILES[$this->key]['tmp_name']);
        }

        return $this->size;
    }

    /**
     * Get temporary file name
     * @return string
     */
    public function getTemporaryFilename()
    {
        return $_FILES[$this->key]['tmp_name'];
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
     */
    public function upload()
    {
        return ($this->validate()) ? $this->storage->upload($this) : false;
    }
}
