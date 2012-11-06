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
    $file->addValidations(array(
        // Ensure file is of type "image/png"
        new \Upload\Validation\Mimetype('image/png'),

        // Ensure file has correct extension
        new \Upload\Validation\Extension('png'),

        // Ensure file is no larger than 5M (use "B", "K", M", or "G")
        new \Upload\Validation\Size('5M')
    ));

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

## Contributions

### Pull Requests

1. Fork this repository
2. Create a new branch for each feature or improvement
3. Send a pull request from each feature branch to the develop branch

It is very important to separate new features or improvements into separate feature branches, and to send a pull
request for each branch. This allows me to review and pull in new features or improvements individually.

### Style Guide

All pull requests must adhere to the PSR-2 standard.

### Unit Testing

All pull requests must be accompanied by passing unit tests and complete code coverage. This repository uses phpunit
and Composer. You must run `composer install` to install this package's dependencies before the unit tests will run.
Unit tests rely on [vfsStream](http://vfs.bovigo.org/) to replicate a physical filesystem in memory.

## Author

[Josh Lockhart](https://github.com/codeguy)

## License

MIT Public License
