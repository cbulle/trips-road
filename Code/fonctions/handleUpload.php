<?php
require_once __DIR__ . '/compressImage.php';
function handleUpload($file, $uploadDir, $thumbDir = null, $prefix = 'file_', $quality=70, $thumbWidth=200){
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = uniqid($prefix) . '.' . $ext;
    $dest = $uploadDir . $newName;
    move_uploaded_file($file['tmp_name'], $dest);
    compressImage($dest, $dest, $quality, $thumbWidth, $thumbDir);
    return $newName;
}