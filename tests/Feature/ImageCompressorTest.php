<?php

namespace Tests\Feature;

use App\Support\ImageCompressor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ImageCompressorTest extends TestCase
{
    private function makeImage(int $w, int $h, string $format): string
    {
        $img = imagecreatetruecolor($w, $h);
        // Contenu varié pour que la compression ait du sens
        for ($i = 0; $i < 400; $i++) {
            imagefilledellipse($img, random_int(0, $w), random_int(0, $h), random_int(20, 400), random_int(20, 400), imagecolorallocate($img, random_int(0, 255), random_int(0, 255), random_int(0, 255)));
        }
        $path = tempnam(sys_get_temp_dir(), 'img').'.'.$format;
        match ($format) {
            'jpg' => imagejpeg($img, $path, 100),
            'png' => imagepng($img, $path, 0),
            'webp' => imagewebp($img, $path, 100),
        };
        imagedestroy($img);

        return $path;
    }

    public function test_large_jpeg_is_downscaled_and_lighter(): void
    {
        $path = $this->makeImage(3600, 2400, 'jpg');
        $before = filesize($path);

        $gain = ImageCompressor::shrink($path);

        clearstatcache();
        $this->assertNotNull($gain);
        [$w, $h] = getimagesize($path);
        $this->assertSame(2000, $w);
        $this->assertSame(1333, $h);
        $this->assertLessThan($before, filesize($path));
        $this->assertSame('image/jpeg', getimagesize($path)['mime']);
        @unlink($path);
    }

    public function test_png_keeps_its_format(): void
    {
        $path = $this->makeImage(2600, 1400, 'png');

        ImageCompressor::shrink($path);

        clearstatcache();
        $info = getimagesize($path);
        $this->assertSame('image/png', $info['mime']);
        $this->assertSame(2000, $info[0]);
        @unlink($path);
    }

    public function test_small_image_is_left_untouched(): void
    {
        $path = $this->makeImage(400, 300, 'jpg');
        $hash = md5_file($path);

        $this->assertNull(ImageCompressor::shrink($path));
        $this->assertSame($hash, md5_file($path));
        @unlink($path);
    }

    public function test_uploaded_images_are_shrunk_before_reaching_the_controller(): void
    {
        Route::post('/_test/upload-size', fn () => response()->json(getimagesize(request()->file('photo')->getPathname())))
            ->middleware('web');

        $path = $this->makeImage(3000, 2000, 'jpg');
        $upload = new UploadedFile($path, 'photo.jpg', 'image/jpeg', null, true);

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class)
            ->post('/_test/upload-size', ['photo' => $upload])
            ->assertOk()
            ->assertJsonPath('0', 2000);
        @unlink($path);
    }
}
