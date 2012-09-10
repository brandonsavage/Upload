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

    // Validate file upload
    $file->addValidations(
        // Ensure file is of type "image/png"
        new \Upload\Validation\MediaType('image/png'),

        // Ensure file is no larger than 5MB
        new \Upload\Validation\FileSize('5MB')
    );

    // Try to upload file
    $result = $file->upload();

    // Report errors if upload failed
    if ( $result === false ) {
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
