<?php

$updir = 'uploadsdir';
$filevar = 'filevar';

if ($_FILES && isset($_FILES[$filevar])) {
	require 'Upload.php';

	if (!file_exists($updir)) mkdir($updir);

	$storage = new \Upload\Storage\FileSystem($updir);
	$file = new \Upload\File('filevar', $storage);

	// Optionally you can rename the file on upload
	$new_filename = uniqid();
	$file->setName($new_filename);

	// Validate file upload
	// MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
	$file->addValidations(array(
	    // Ensure file mime type is either "png", "jpg" or "gif"
	    new \Upload\Validation\Mimetype(array('image/png', 'image/jpeg', 'image/gif')),

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
}

?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="filevar" accept="image/*" />
    <input type="submit" value="Upload File"/>
</form>