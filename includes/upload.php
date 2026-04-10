<?php
function ensure_upload_directory()
{
    if (!is_dir(UPLOAD_PRODUCT_DIR)) {
        mkdir(UPLOAD_PRODUCT_DIR, 0777, true);
    }
}

function upload_product_image(array $file)
{
    ensure_upload_directory();

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return array('success' => false, 'error' => 'Please choose an image file.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'error' => 'Image upload failed.');
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return array('success' => false, 'error' => 'Image size must be under 5MB.');
    }

    $allowedExtensions = array('jpg', 'jpeg', 'png', 'webp', 'gif');
    $allowedMimeTypes = array('image/jpeg', 'image/png', 'image/webp', 'image/gif');
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, $allowedExtensions, true)) {
        return array('success' => false, 'error' => 'Only JPG, PNG, WEBP and GIF images are allowed.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';

    if ($finfo) {
        finfo_close($finfo);
    }

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return array('success' => false, 'error' => 'Invalid image type uploaded.');
    }

    $filename = time() . '_' . uniqid() . '.' . $extension;
    $destination = UPLOAD_PRODUCT_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return array('success' => false, 'error' => 'Unable to save uploaded image.');
    }

    return array(
        'success' => true,
        'path' => 'uploads/products/' . $filename
    );
}

function upload_product_gallery(array $files)
{
    $paths = array();
    $errors = array();

    if (!isset($files['name']) || !is_array($files['name'])) {
        return array($paths, $errors);
    }

    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        if (!isset($files['error'][$i]) || $files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $single = array(
            'name' => $files['name'][$i],
            'type' => $files['type'][$i],
            'tmp_name' => $files['tmp_name'][$i],
            'error' => $files['error'][$i],
            'size' => $files['size'][$i]
        );
        $upload = upload_product_image($single);

        if ($upload['success']) {
            $paths[] = $upload['path'];
        } else {
            $errors[] = $upload['error'];
        }
    }

    return array($paths, $errors);
}

function delete_uploaded_image($relativePath)
{
    if (!$relativePath) {
        return;
    }

    $filename = basename($relativePath);

    if (strpos($filename, 'placeholder-') === 0) {
        return;
    }

    $absolutePath = BASE_PATH . '/' . ltrim($relativePath, '/');

    if (is_file($absolutePath)) {
        unlink($absolutePath);
    }
}
