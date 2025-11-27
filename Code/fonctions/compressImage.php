<?php
// Fonction de compression + création miniature
function compressImage($source, $dest, $quality = 75, $thumbWidth = 200, $thumbDir = null) {
    $info = getimagesize($source);
    $mime = $info['mime'];

    if($mime == 'image/jpeg'){
        $img = imagecreatefromjpeg($source);
        imagejpeg($img, $dest, $quality);
    } elseif($mime == 'image/png'){
        $img = imagecreatefrompng($source);
        $pngQuality = 9 - floor(($quality / 100) * 9);
        imagepng($img, $dest, $pngQuality);
    } else {
        return false; // format non supporté
    }

    // création miniature
    if($thumbDir){
        $ratio = $info[0]/$info[1];
        $thumbHeight = intval($thumbWidth / $ratio);
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $info[0], $info[1]);
        $thumbPath = $thumbDir . basename($dest);
        if($mime == 'image/jpeg') imagejpeg($thumb, $thumbPath, $quality);
        elseif($mime == 'image/png') imagepng($thumb, $thumbPath, $pngQuality);
        imagedestroy($thumb);
    }

    imagedestroy($img);
    return true;
}