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
class FileSize extends \Upload\Validation\Base
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
     * Bytes per unit lookup table
     * @var array
     */
    protected $units = array(
        'B' => 1,
        'KB' => 1024,
        'MB' => 1048576
    );

    /**
     * Error message
     * @var string
     */
    protected $message = 'Invalid file size';

    /**
     * Constructor
     * @param int $maxSize Maximum acceptable file size in bytes (inclusive)
     * @param int $minSize Minimum acceptable file size in bytes (inclusive)
     */
    public function __construct($maxSize, $minSize = 0)
    {
        if (is_string($maxSize)) {
            $maxSize = $this->humanReadableToBytes($maxSize);
        }
        $this->maxSize = $maxSize;

        if (is_string($minSize)) {
            $minSize = $this->humanReadableToBytes($minSize);
        }
        $this->minSize = $minSize;
    }

    /**
     * Validate
     * @param  \Upload\File $file
     * @return bool
     */
    public function validate(\Upload\File $file)
    {
        $fileSize = $file->getSize();
        $isValid = true;

        if ($fileSize < $this->minSize) {
            $this->setMessage('File size is too small');
            $isValid = false;
        }

        if ($fileSize > $this->maxSize) {
            $this->setMessage('File size is too large');
            $isValid = false;
        }

        return $isValid;
    }

    /**
     * Convert human readble file size into bytes
     * @param  text $input Human readable filesize (e.g. "1MB")
     * @return int
     */
    protected function humanReadableToBytes($input)
    {
        preg_match('#(\d+)\s*(B|KB|MB)#', $input, $parts);
        $number = (int)$parts[1];
        $unit = $parts[2];
        $bytesPerUnit = $this->units[$unit];

        return $number * $bytesPerUnit;
    }
}
