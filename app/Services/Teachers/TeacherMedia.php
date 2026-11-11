<?php

namespace App\Services\Teachers;

use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Teacher photo and logo uploads, resized with PHP's built-in GD.
 *
 *  photo -> 512 x 512 centre-cropped JPEG
 *  logo  -> fits inside 512 x 512, PNG (keeps transparency)
 *
 * Files live on the "public" disk under teachers/{id}/.
 */
class TeacherMedia
{
    public const PHOTO_SIZE = 512;

    public const LOGO_MAX = 512;

    public function storePhoto(Teacher $teacher, UploadedFile $file): void
    {
        $source = $this->load($file);
        $image = $this->cropSquare($source, self::PHOTO_SIZE);

        $path = $this->save($teacher, 'photo', $image, 'jpg');

        $this->replace($teacher, 'photo_path', $path);
    }

    public function storeLogo(Teacher $teacher, UploadedFile $file): void
    {
        $source = $this->load($file);
        $image = $this->fitInside($source, self::LOGO_MAX);

        $path = $this->save($teacher, 'logo', $image, 'png');

        $this->replace($teacher, 'logo_path', $path);
    }

    public function removePhoto(Teacher $teacher): void
    {
        $this->replace($teacher, 'photo_path', null);
    }

    public function removeLogo(Teacher $teacher): void
    {
        $this->replace($teacher, 'logo_path', null);
    }

    private function load(UploadedFile $file): \GdImage
    {
        $contents = file_get_contents($file->getRealPath());
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw new RuntimeException('The uploaded file is not a readable image.');
        }

        return $image;
    }

    private function cropSquare(\GdImage $source, int $size): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $side = min($width, $height);

        $target = imagecreatetruecolor($size, $size);
        imagefill($target, 0, 0, imagecolorallocate($target, 255, 255, 255));

        imagecopyresampled(
            $target, $source,
            0, 0,
            intdiv($width - $side, 2), intdiv($height - $side, 2),
            $size, $size,
            $side, $side,
        );

        return $target;
    }

    private function fitInside(\GdImage $source, int $max): \GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $max / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));

        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        return $target;
    }

    private function save(Teacher $teacher, string $kind, \GdImage $image, string $extension): string
    {
        ob_start();
        $extension === 'png' ? imagepng($image, null, 6) : imagejpeg($image, null, 85);
        $binary = (string) ob_get_clean();

        // A new random name each time, so browsers never show a cached old image.
        $path = "teachers/{$teacher->getKey()}/{$kind}-".Str::lower(Str::random(10)).".{$extension}";
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function replace(Teacher $teacher, string $column, ?string $path): void
    {
        $old = $teacher->getAttribute($column);

        $teacher->forceFill([$column => $path])->save();

        if ($old && $old !== $path) {
            Storage::disk('public')->delete($old);
        }
    }
}
