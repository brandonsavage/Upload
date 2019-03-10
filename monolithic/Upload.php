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
 * Autoloader
 *
 * This class provides a default PSR-0 autoloader if not using Composer.
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class Autoloader
{
    /**
     * The project's base directory
     * @var string
     */
    static protected $base;

    /**
     * Register autoloader
     */
    static public function register()
    {
        self::$base = dirname(__FILE__) . '/../';
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Autoload classname
     * @param  string $className The class to load
     */
    static public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require self::$base . $fileName;
    }
}
?><?php
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
?><?php
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
 * FileInfo Interface
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   2.0.0
 * @package Upload
 */
interface FileInfoInterface
{
    public function getPathname();

    public function getName();

    public function setName($name);

    public function getExtension();

    public function setExtension($extension);

    public function getNameWithExtension();

    public function getMimetype();

    public function getSize();

    public function getMd5();

    public function getDimensions();

    public function isUploadedFile();
}
?><?php
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
 * File Information
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   2.0.0
 * @package Upload
 */
class FileInfo extends \SplFileInfo implements \Upload\FileInfoInterface
{
    /**
     * Factory method that returns new instance of \FileInfoInterface
     * @var callable
     */
    protected static $factory;

    /**
     * File name (without extension)
     * @var string
     */
    protected $name;

    /**
     * File extension (without dot prefix)
     * @var string
     */
    protected $extension;

    /**
     * File mimetype
     * @var string
     */
    protected $mimetype;

    /**
     * Constructor
     *
     * @param string $filePathname Absolute path to uploaded file on disk
     * @param string $newName      Desired file name (with extension) of uploaded file
     */
    public function __construct($filePathname, $newName = null)
    {
        $desiredName = is_null($newName) ? $filePathname : $newName;
        $this->setName(pathinfo($desiredName, PATHINFO_FILENAME));
        $this->setExtension(pathinfo($desiredName, PATHINFO_EXTENSION));

        parent::__construct($filePathname);
    }

    /**
     * Get file name (without extension)
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set file name (without extension)
     * 
     * It also makes sure file name is safe
     *
     * @param  string           $name
     * @return \Upload\FileInfo Self
     */
    public function setName($name)
    {
        $name = preg_replace("/([^\w\s\d\-_~,;:\[\]\(\).]|[\.]{2,})/", "", $name);
        $name = basename($name);
        $this->name = $name;

        return $this;
    }

    /**
     * Get file extension (without dot prefix)
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Set file extension (without dot prefix)
     *
     * @param  string           $extension
     * @return \Upload\FileInfo Self
     */
    public function setExtension($extension)
    {
        $this->extension = strtolower($extension);

        return $this;
    }

    /**
     * Get file name with extension
     *
     * @return string
     */
    public function getNameWithExtension()
    {
        return $this->extension === '' ? $this->name : sprintf('%s.%s', $this->name, $this->extension);
    }

    /**
     * Get mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        if (isset($this->mimetype) === false) {
            $finfo = new \finfo(FILEINFO_MIME);
            $mimetype = $finfo->file($this->getPathname());
            $mimetypeParts = preg_split('/\s*[;,]\s*/', $mimetype);
            $this->mimetype = strtolower($mimetypeParts[0]);
            unset($finfo);
        }

        return $this->mimetype;
    }

    /**
     * Get md5
     *
     * @return string
     */
    public function getMd5()
    {
        return md5_file($this->getPathname());
    }

    /**
     * Get a specified hash
     *
     * @return string
     */
    public function getHash($algorithm = 'md5')
    {
        return hash_file($algorithm, $this->getPathname());
    }

    /**
     * Get image dimensions
     *
     * @return array formatted array of dimensions
     */
    public function getDimensions()
    {
        list($width, $height) = getimagesize($this->getPathname());

        return array(
            'width' => $width,
            'height' => $height
        );
    }

    /**
     * Is this file uploaded with a POST request?
     *
     * This is a separate method so that it can be stubbed in unit tests to avoid
     * the hard dependency on the `is_uploaded_file` function.
     *
     * @return bool
     */
    public function isUploadedFile()
    {
        return is_uploaded_file($this->getPathname());
    }

    public static function setFactory($callable)
    {
        if (is_object($callable) === false || method_exists($callable, '__invoke') === false) {
            throw new \InvalidArgumentException('Callback is not a Closure or invokable object.');
        }

        static::$factory = $callable;
    }

    public static function createFromFactory($tmpName, $name = null) {
        if (isset(static::$factory) === true) {
            $result = call_user_func_array(static::$factory, array($tmpName, $name));
            if ($result instanceof \Upload\FileInfoInterface === false) {
                throw new \RuntimeException('FileInfo factory must return instance of \Upload\FileInfoInterface.');
            }

            return $result;
        }

        return new static($tmpName, $name);
    }
}
?><?php
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
?><?php
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
 * Storage Interface
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   2.0.0
 * @package Upload
 */
interface StorageInterface
{
    /**
     * Upload file
     *
     * This method is responsible for uploading an `\Upload\FileInfoInterface` instance
     * to its intended destination. If upload fails, an exception should be thrown.
     *
     * @param  \Upload\FileInfoInterface $fileInfo
     * @throws \Exception                If upload fails
     */
    public function upload(\Upload\FileInfoInterface $fileInfo);
}
?><?php
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
 * Validation Interface
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   2.0.0
 * @package Upload
 */
interface ValidationInterface
{
    /**
     * Validate file
     *
     * This method is responsible for validating an `\Upload\FileInfoInterface` instance.
     * If validation fails, an exception should be thrown.
     *
     * @param  \Upload\FileInfoInterface $fileInfo
     * @throws \Exception                If validation fails
     */
    public function validate(\Upload\FileInfoInterface $fileInfo);
}
?><?php
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
namespace Upload\Storage;

/**
 * FileSystem Storage
 *
 * This class uploads files to a designated directory on the filesystem.
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class FileSystem implements \Upload\StorageInterface
{
    /**
     * Path to upload destination directory (with trailing slash)
     * @var string
     */
    protected $directory;

    /**
     * Overwrite existing files?
     * @var bool
     */
    protected $overwrite;

    /**
     * Constructor
     *
     * @param  string                    $directory Relative or absolute path to upload directory
     * @param  bool                      $overwrite Should this overwrite existing files?
     * @throws \InvalidArgumentException            If directory does not exist
     * @throws \InvalidArgumentException            If directory is not writable
     */
    public function __construct($directory, $overwrite = false)
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException('Directory does not exist');
        }
        if (!is_writable($directory)) {
            throw new \InvalidArgumentException('Directory is not writable');
        }
        $this->directory = rtrim($directory, '/') . DIRECTORY_SEPARATOR;
        $this->overwrite = (bool)$overwrite;
    }

    /**
     * Upload
     *
     * @param  \Upload\FileInfoInterface $file The file object to upload
     * @throws \Upload\Exception               If overwrite is false and file already exists
     * @throws \Upload\Exception               If error moving file to destination
     */
    public function upload(\Upload\FileInfoInterface $fileInfo)
    {
        $destinationFile = $this->directory . $fileInfo->getNameWithExtension();
        if ($this->overwrite === false && file_exists($destinationFile) === true) {
            throw new \Upload\Exception('File already exists', $fileInfo);
        }

        if ($this->moveUploadedFile($fileInfo->getPathname(), $destinationFile) === false) {
            throw new \Upload\Exception('File could not be moved to final destination.', $fileInfo);
        }
    }

    /**
     * Move uploaded file
     *
     * This method allows us to stub this method in unit tests to avoid
     * hard dependency on the `move_uploaded_file` function.
     *
     * @param  string $source      The source file
     * @param  string $destination The destination file
     * @return bool
     */
    protected function moveUploadedFile($source, $destination)
    {
        return move_uploaded_file($source, $destination);
    }
}
?><?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfoInterface;
use Upload\ValidationInterface;

class Dimensions implements ValidationInterface
{
    /**
     * @var integer
     */
    protected $width;

    /**
     * @var integer
     */
    protected $height;

    /**
     * @param int $expectedWidth
     * @param int $expectedHeight
     */
    function __construct($expectedWidth, $expectedHeight)
    {
        $this->width = $expectedWidth;
        $this->height = $expectedHeight;
    }

    /**
     * @inheritdoc
     */
    public function validate(FileInfoInterface $info)
    {
        $dimensions = $info->getDimensions();
        $filename = $info->getNameWithExtension();
        if (!$dimensions) {
            throw new Exception(sprintf('%s: Could not detect image size.', $filename));
        }
        if ($dimensions['width'] != $this->width) {
            throw new Exception(
                sprintf(
                    '%s: Image width(%dpx) does not match required width(%dpx)',
                    $filename,
                    $dimensions['width'],
                    $this->width
                )
            );
        }
        if ($dimensions['height'] != $this->height) {
            throw new Exception(
                sprintf(
                    '%s: Image height(%dpx) does not match required height(%dpx)',
                    $filename,
                    $dimensions['height'],
                    $this->height
                )
            );
        }
    }
}
?><?php
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
namespace Upload\Validation;

/**
 * Validate File Extension
 *
 * This class validates an uploads file extension. It takes file extension with out dot
 * or array of extensions. For example: 'png' or array('jpg', 'png', 'gif').
 *
 * WARNING! Validation only by file extension not very secure.
 *
 * @author  Alex Kucherenko <kucherenko.email@gmail.com>
 * @package Upload
 */
class Extension implements \Upload\ValidationInterface
{
    /**
     * Array of acceptable file extensions without leading dots
     * @var array
     */
    protected $allowedExtensions;

    /**
     * Constructor
     *
     * @param string|array $allowedExtensions Allowed file extensions
     * @example new \Upload\Validation\Extension(array('png','jpg','gif'))
     * @example new \Upload\Validation\Extension('png')
     */
    public function __construct($allowedExtensions)
    {
        if (is_string($allowedExtensions) === true) {
            $allowedExtensions = array($allowedExtensions);
        }

        $this->allowedExtensions = array_map('strtolower', $allowedExtensions);
    }

    /**
     * Validate
     *
     * @param  \Upload\FileInfoInterface $fileInfo
     * @throws \RuntimeException         If validation fails
     */
    public function validate(\Upload\FileInfoInterface $fileInfo)
    {
        $fileExtension = strtolower($fileInfo->getExtension());

        if (in_array($fileExtension, $this->allowedExtensions) === false) {
            throw new \Upload\Exception(sprintf('Invalid file extension. Must be one of: %s', implode(', ', $this->allowedExtensions)), $fileInfo);
        }
    }
}
?><?php
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
namespace Upload\Validation;

/**
 * Validate Upload Media Type
 *
 * This class validates an upload's media type (e.g. "image/png").
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class Mimetype implements \Upload\ValidationInterface
{
    /**
     * Valid media types
     * @var array
     */
    protected $mimetypes;

    /**
     * Constructor
     *
     * @param string|array $mimetypes
     */
    public function __construct($mimetypes)
    {
        if (is_string($mimetypes) === true) {
            $mimetypes = array($mimetypes);
        }
        $this->mimetypes = $mimetypes;
    }

    /**
     * Validate
     *
     * @param  \Upload\FileInfoInterface  $fileInfo
     * @throws \RuntimeException          If validation fails
     */
    public function validate(\Upload\FileInfoInterface $fileInfo)
    {
        if (in_array($fileInfo->getMimetype(), $this->mimetypes) === false) {
            throw new \Upload\Exception(sprintf('Invalid mimetype. Must be one of: %s', implode(', ', $this->mimetypes)), $fileInfo);
        }
    }
}
?><?php
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
namespace Upload\Validation;

/**
 * Validate Upload File Size
 *
 * This class validates an uploads file size using maximum and (optionally)
 * minimum file size bounds (inclusive). Specify acceptable file sizes
 * as an integer (in bytes) or as a human-readable string (e.g. "5MB").
 *
 * @author  Josh Lockhart <info@joshlockhart.com>
 * @since   1.0.0
 * @package Upload
 */
class Size implements \Upload\ValidationInterface
{
    /**
     * Minimum acceptable file size (bytes)
     * @var int
     */
    protected $minSize;

    /**
     * Maximum acceptable file size (bytes)
     * @var int
     */
    protected $maxSize;

    /**
     * Constructor
     *
     * @param int $maxSize Maximum acceptable file size in bytes (inclusive)
     * @param int $minSize Minimum acceptable file size in bytes (inclusive)
     */
    public function __construct($maxSize, $minSize = 0)
    {
        if (is_string($maxSize)) {
            $maxSize = \Upload\File::humanReadableToBytes($maxSize);
        }
        $this->maxSize = $maxSize;

        if (is_string($minSize)) {
            $minSize = \Upload\File::humanReadableToBytes($minSize);
        }
        $this->minSize = $minSize;
    }

    /**
     * Validate
     *
     * @param  \Upload\FileInfoInterface  $fileInfo
     * @throws \RuntimeException          If validation fails
     */
    public function validate(\Upload\FileInfoInterface $fileInfo)
    {
        $fileSize = $fileInfo->getSize();

        if ($fileSize < $this->minSize) {
            throw new \Upload\Exception(sprintf('File size is too small. Must be greater than or equal to: %s', $this->minSize), $fileInfo);
        }

        if ($fileSize > $this->maxSize) {
            throw new \Upload\Exception(sprintf('File size is too large. Must be less than: %s', $this->maxSize), $fileInfo);
        }
    }
}
?>