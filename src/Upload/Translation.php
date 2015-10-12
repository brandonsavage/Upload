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
 * Translation
 *
 * This class provides the implementation for translated messages.
 *
 * @author  Ramiro Varandas Jr <ramirovjnr@gmail.com>
 * @package Upload
 */
class Translation
{
    /**
     * Translation messages
     * @var array
     */
    protected $messages;

    /**
     * Constructor
     * @param  string                    $language Language prefix (i.e.: en, pt, pt-BR)
     * @throws \InvalidArgumentException If translation file does not exist
     */
    public function __construct($language)
    {
        $this->loadTranslationFile($language);
    }

    /**
     * Load a translation file containing the messages used by the library
     * @param string $language
     * @throws \InvalidArgumentException If translation file does not exist
     */
    protected function loadTranslationFile($language)
    {
        $filename = __DIR__ . "/Language/${language}.php";

        if (file_exists($filename) === false) {
            throw new \InvalidArgumentException("Cannot find translation file for language: $language");
        }

        $this->messages = require $filename;
    }

    /**
     * Get a translation message
     * @param string $key Message key
     * @param array  $params Array containing positional placeholders values
     * @return string
     */
    public function getMessage($key, $params = array())
    {
        if (array_key_exists($key, $this->messages) === true) {
            return vsprintf($this->messages[$key], $params);
        } else {
            return $key;
        }
    }

}
