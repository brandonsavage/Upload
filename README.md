[![Build Status](https://img.shields.io/travis/codeguy/Upload/2.0.svg?style=flat)](https://travis-ci.org/codeguy/Upload)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/codeguy/Upload.svg?style=flat)](https://scrutinizer-ci.com/g/codeguy/Upload/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/codeguy/Upload.svg?style=flat)](https://scrutinizer-ci.com/g/codeguy/Upload)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat)](LICENSE.md)
[![Latest Version](https://img.shields.io/github/release/codeguy/Upload.svg?style=flat)](https://github.com/codeguy/Upload/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/codeguy/Upload.svg?style=flat)](https://packagist.org/packages/codeguy/Upload)

# Upload

This component simplifies file validation and uploading.

## Usage

Assume a file is uploaded with this HTML form:

```html
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foo" value=""/>
    <input type="submit" value="Upload File"/>
</form>
```

When the HTML form is submitted, the server-side PHP code can validate and upload the file like this:

```php
<?php
$storage = new \Upload\Storage\FileSystem('/path/to/directory');
$file = new \Upload\File('foo', $storage);

// Optionally you can rename the file on upload
$new_filename = uniqid();
$file->setName($new_filename);

// Validate file upload
// MimeType List => http://www.webmaster-toolkit.com/mime-types.shtml
$file->addValidations(array(
    // Ensure file is of type "image/png"
    new \Upload\Validation\Mimetype('image/png'),

    //You can also add multi mimetype validation
    //new \Upload\Validation\Mimetype(array('image/png', 'image/gif')));

    // Ensure file is no larger than 5M (use "B", "K", M", or "G")
    new \Upload\Validation\Size('5M')
));

// Access data about the file that has been uploaded
$data = array(
    'name'       => $file->getNameWithExtension(),
    'extension'  => $file->getExtension(),
    'mime'       => $file->getMimetype(),
    'size'       => $file->getSize(),
    'md5'        => $file->getMd5(),
    'dimensions' => $file->getDimensions()
);

// Try to upload file
try {
    // Success!
    $file->upload();
} catch (\Exception $e) {
    // Fail!
    $errors = $file->getErrors();
}
```

## How to Install

Install composer in your project:

```
curl -s https://getcomposer.org/installer | php
```

Create a composer.json file in your project root:
```json
{
    "require": {
        "codeguy/upload": "*"
    }
}
```
Install via composer:
```
php composer.phar install
```
## Author

[Josh Lockhart](https://github.com/codeguy)

## License

MIT Public License
