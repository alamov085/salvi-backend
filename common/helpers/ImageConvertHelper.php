<?php

namespace common\helpers;

class ImageConvertHelper
{
    /**
     * @param string $src path to the source PNG file
     * @param string $dest path to the target JPG file
     * @return bool
     */
    public static function convertPngToJpg(string $src, string $dest, int $quality = 90): bool
    {
        if (!file_exists($src)) {
            return false;
        }

        $info = getimagesize($src);
        if (!$info || $info[2] !== IMAGETYPE_PNG) {
            return false;
        }

        $png = imagecreatefrompng($src);
        if (!$png) {
            return false;
        }

        $width = imagesx($png);
        $height = imagesy($png);

        $jpg = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($jpg, 255, 255, 255);
        imagefill($jpg, 0, 0, $white);
        imagecopy($jpg, $png, 0, 0, 0, 0, $width, $height);

        $result = imagejpeg($jpg, $dest, $quality);

        imagedestroy($png);
        imagedestroy($jpg);

        return $result;
    }
}
