# Upload

## Usage

This component simplifies file validation and uploading. Assume a file is uploaded with this HTML form:

    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="foo" value=""/>
        <input type="submit" value="Upload File"/>
    </form>

When the HTML form is submitted, the server-side PHP code can validate and upload the file like this:

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

        // Ensure file is no larger than 5M (use "B", "K", M", or "G")
        new \Upload\Validation\Size('5M')
    ));

    // Access data about the file that has been uploaded
    $data = array(
        'name' => $file->getNameWithExtension(),
        'extension' => $file->getExtension(),
        'mime' => $file->getMimetype(),
        'size' => $file->getSize(),
        'md5' => $file->getMd5(),
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

## How to Install

Install composer in your project:

    curl -s https://getcomposer.org/installer | php

Create a composer.json file in your project root:

    {
        "require": {
            "codeguy/upload": "*"
        }
    }

Install via composer:

    php composer.phar install

## Author

[Josh Lockhart](https://github.com/codeguy)

## License

MIT Public License
