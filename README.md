# Upload

[![Build Status](https://img.shields.io/travis/brandonsavage/Upload.svg?style=flat-square)](https://travis-ci.org/brandonsavage/Upload)
[![Latest Version](https://img.shields.io/github/release/brandonsavage/Upload.svg?style=flat-square)](https://github.com/brandonsavage/Upload/releases)
[![Total Downloads](https://img.shields.io/packagist/dt/codeguy/upload.svg?style=flat-square)](https://packagist.org/packages/codeguy/upload)

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
// MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
$file->addValidations(array(
    // Ensure file is of type "image/png"
    new \Upload\Validation\Mimetype('image/png'),

    //You can also add multi mimetype validation
    //new \Upload\Validation\Mimetype(array('image/png', 'image/gif'))

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

Require the package with composer:

```
php composer.phar require codeguy/upload
```

## Author

[Josh Lockhart](https://github.com/codeguy)

## License

MIT Public License
