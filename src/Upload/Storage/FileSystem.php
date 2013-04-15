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
class FileSystem extends \Upload\Storage\Base
{
    /**
     * Upload directory
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
     * @param  string                       $directory      Relative or absolute path to upload directory
     * @param  bool                         $overwrite      Should this overwrite existing files?
     * @throws \InvalidArgumentException                    If directory does not exist
     * @throws \InvalidArgumentException                    If directory is not writable
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
        $this->overwrite = $overwrite;
    }

    /**
     * Upload
     * @param  \Upload\File $file The file object to upload
     * @param  string $newName Give the file it a new name
     * @return bool
     * @throws \RuntimeException   If overwrite is false and file already exists
     */
    public function upload(\Upload\File $file, $newName = null)
    {
        if (is_string($newName)) {
            $fileName = strpos($newName, '.') ? $newName : $newName.'.'.$file->getExtension();

        } else {
            $fileName = $file->getNameWithExtension();
        }

        $newFile = $this->directory . $fileName;
        if ($this->overwrite === false && file_exists($newFile)) {
            $file->addError('File already exists');
            throw new \Upload\Exception\UploadException('File already exists');
        }

        return $this->moveUploadedFile($file->getPathname(), $newFile);
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
