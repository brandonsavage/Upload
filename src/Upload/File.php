<?php
/**
 * Upload
 *
 * @author      Josh Lockhart <info@joshlockhart.com>
 * @copyright   2012 Josh Lockhart
 * @link        http://www.joshlockhart.com
 * @version     2.0.0
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
class File implements \ArrayAccess, \IteratorAggregate, \Countable
{
    /**
     * Upload error code messages
     * @var array
     */
    protected static $errorCodeMessages = array(
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk',
        8 => 'A PHP extension stopped the file upload'
    );

    /**
     * Storage delegate
     * @var \Upload\StorageInterface
     */
    protected $storage;

    /**
     * File information
     * @var array[\Upload\FileInfoInterface]
     */
    protected $objects = array();

    /**
     * Validations
     * @var array[\Upload\ValidationInterface]
     */
    protected $validations = array();

    /**
     * Validation errors
     * @var array[String]
     */
    protected $errors = array();

    /**
     * Before validation callback
     * @var callable
     */
    protected $beforeValidationCallback;

    /**
     * After validation callback
     * @var callable
     */
    protected $afterValidationCallback;

    /**
     * Before upload callback
     * @var callable
     */
    protected $beforeUploadCallback;

    /**
     * After upload callback
     * @var callable
     */
    protected $afterUploadCallback;

    /**
     * Constructor
     *
     * @param  string                    $key     The $_FILES[] key
     * @param  \Upload\StorageInterface  $storage The upload delegate instance
     * @throws \RuntimeException                  If file uploads are disabled in the php.ini file
     * @throws \InvalidArgumentException          If $_FILES[] does not contain key
     */
    public function __construct($key, \Upload\StorageInterface $storage)
    {
        // Check if file uploads are allowed
        if (ini_get('file_uploads') == false) {
            throw new \RuntimeException('File uploads are disabled in your PHP.ini file');
        }

        // Check if key exists
        if (isset($_FILES[$key]) === false) {
            throw new \InvalidArgumentException("Cannot find uploaded file(s) identified by key: $key");
        }

        // Collect file info
        if (is_array($_FILES[$key]['tmp_name']) === true) {
            foreach ($_FILES[$key]['tmp_name'] as $index => $tmpName) {
                if ($_FILES[$key]['error'][$index] !== UPLOAD_ERR_OK) {
                    $this->errors[] = sprintf(
                        '%s: %s',
                        $_FILES[$key]['name'][$index],
                        static::$errorCodeMessages[$_FILES[$key]['error'][$index]]
                    );
                    continue;
                }

                $this->objects[] = \Upload\FileInfo::createFromFactory(
                    $_FILES[$key]['tmp_name'][$index],
                    $_FILES[$key]['name'][$index]
                );
            }
        } else {
            if ($_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = sprintf(
                    '%s: %s',
                    $_FILES[$key]['name'],
                    static::$errorCodeMessages[$_FILES[$key]['error']]
                );
            }

            $this->objects[] = \Upload\FileInfo::createFromFactory(
                $_FILES[$key]['tmp_name'],
                $_FILES[$key]['name']
            );
        }

        $this->storage = $storage;
    }

    /********************************************************************************
     * Callbacks
     *******************************************************************************/

    /**
     * Set `beforeValidation` callable
     *
     * @param  callable                  $callable Should accept one `\Upload\FileInfoInterface` argument
     * @return \Upload\File                        Self
     * @throws \InvalidArgumentException           If argument is not a Closure or invokable object
     */
    public function beforeValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->beforeValidation = $callable;

        return $this;
    }

    /**
     * Set `afterValidation` callable
     *
     * @param  callable                  $callable Should accept one `\Upload\FileInfoInterface` argument
     * @return \Upload\File                        Self
     * @throws \InvalidArgumentException           If argument is not a Closure or invokable object
     */
    public function afterValidate($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->afterValidation = $callable;

        return $this;
    }

    /**
     * Set `beforeUpload` callable
     *
     * @param  callable                  $callable Should accept one `\Upload\FileInfoInterface` argument
     * @return \Upload\File                        Self
     * @throws \InvalidArgumentException           If argument is not a Closure or invokable object
     */
    public function beforeUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->beforeUpload = $callable;

        return $this;
    }

    /**
     * Set `afterUpload` callable
     *
     * @param  callable                  $callable Should accept one `\Upload\FileInfoInterface` argument
     * @return \Upload\File                        Self
     * @throws \InvalidArgumentException           If argument is not a Closure or invokable object
     */
    public function afterUpload($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }
        $this->afterUpload = $callable;

        return $this;
    }

    /**
     * Apply callable
     *
     * @param  string                    $callbackName
     * @param  \Upload\FileInfoInterface $file
     * @return \Upload\File              Self
     */
    protected function applyCallback($callbackName, \Upload\FileInfoInterface $file)
    {
        if (in_array($callbackName, array('beforeValidation', 'afterValidation', 'beforeUpload', 'afterUpload')) === true) {
            if (isset($this->$callbackName) === true) {
                call_user_func_array($this->$callbackName, array($file));
            }
        }
    }

    /********************************************************************************
     * Validation and Error Handling
     *******************************************************************************/

    /**
     * Add file validations
     *
     * @param  array[\Upload\ValidationInterface] $validations
     * @return \Upload\File                       Self
     */
    public function addValidations(array $validations)
    {
        foreach ($validations as $validation) {
            $this->addValidation($validation);
        }

        return $this;
    }

    /**
     * Add file validation
     *
     * @param  \Upload\ValidationInterface $validation
     * @return \Upload\File                Self
     */
    public function addValidation(\Upload\ValidationInterface $validation)
    {
        $this->validations[] = $validation;

        return $this;
    }

    /**
     * Get file validations
     *
     * @return array[\Upload\ValidationInterface]
     */
    public function getValidations()
    {
        return $this->validations;
    }

    /**
     * Is this collection valid and without errors?
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->objects as $fileInfo) {
            // Before validation callback
            $this->applyCallback('beforeValidation', $fileInfo);

            // Check is uploaded file
            if ($fileInfo->isUploadedFile() === false) {
                $this->errors[] = sprintf(
                    '%s: %s',
                    $fileInfo->getNameWithExtension(),
                    'Is not an uploaded file'
                );
                continue;
            }

            // Apply user validations
            foreach ($this->validations as $validation) {
                try {
                    $validation->validate($fileInfo);
                } catch (\Upload\Exception $e) {
                    $this->errors[] = sprintf(
                        '%s: %s',
                        $fileInfo->getNameWithExtension(),
                        $e->getMessage()
                    );
                }
            }

            // After validation callback
            $this->applyCallback('afterValidation', $fileInfo);
        }

        return empty($this->errors);
    }

    /**
     * Get file validation errors
     *
     * @return array[String]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /********************************************************************************
     * Helper Methods
     *******************************************************************************/

    public function __call($name, $arguments)
    {
        $count = count($this->objects);
        $result = null;

        if ($count) {
            if ($count > 1) {
                $result = array();
                foreach ($this->objects as $object) {
                    $result[] = call_user_func_array(array($object, $name), $arguments);
                }
            } else {
                $result = call_user_func_array(array($this->objects[0], $name), $arguments);
            }
        }

        return $result;
    }

    /********************************************************************************
    * Upload
    *******************************************************************************/

    /**
     * Upload file (delegated to storage object)
     *
     * @return bool
     * @throws \Upload\Exception If validation fails
     * @throws \Upload\Exception If upload fails
     */
    public function upload()
    {
        if ($this->isValid() === false) {
            throw new \Upload\Exception('File validation failed');
        }

        foreach ($this->objects as $fileInfo) {
            $this->applyCallback('beforeUpload', $fileInfo);
            $this->storage->upload($fileInfo);
            $this->applyCallback('afterUpload', $fileInfo);
        }

        return true;
    }

    /********************************************************************************
     * Array Access Interface
     *******************************************************************************/

    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->objects[$offset]) ? $this->objects[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->objects[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->objects[$offset]);
    }

    /********************************************************************************
     * Iterator Aggregate Interface
     *******************************************************************************/

    public function getIterator()
    {
        return new \ArrayIterator($this->objects);
    }

    /********************************************************************************
     * Countable Interface
     *******************************************************************************/

    public function count()
    {
        return count($this->objects);
    }

    /********************************************************************************
    * Helpers
    *******************************************************************************/

    /**
     * Convert human readable file size (e.g. "10K" or "3M") into bytes
     *
     * @param  string $input
     * @return int
     */
    public static function humanReadableToBytes($input)
    {
        $number = (int)$input;
        $units = array(
            'b' => 1,
            'k' => 1024,
            'm' => 1048576,
            'g' => 1073741824
        );
        $unit = strtolower(substr($input, -1));
        if (isset($units[$unit])) {
            $number = $number * $units[$unit];
        }

        return $number;
    }
}
