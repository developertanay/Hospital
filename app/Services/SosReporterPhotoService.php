<?php
namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/** A fresh SOS sender photo stays on the private local disk, outside public storage. */
class SosReporterPhotoService
{
    public function validate(Request $request)
    {
        $input=$request->validate(['reporter_photo_base64'=>'required|string|max:2796204']);
        $bytes=base64_decode($input['reporter_photo_base64'],true);
        $image=$bytes===false ? false : @getimagesizefromstring($bytes);
        if (!$image || strlen($bytes)>2097152 || !in_array($image['mime'],['image/jpeg','image/png','image/webp'],true)
            || $image[0]<1 || $image[1]<1 || $image[0]>4096 || $image[1]>4096 || $image[0]*$image[1]>6000000) {
            $this->invalid();
        }
        if (!function_exists('imagecreatefromstring')) throw new RuntimeException('The server camera-photo processor is unavailable. Please contact support.');
        $decoded=@imagecreatefromstring($bytes);
        if (!$decoded) $this->invalid();
        $decoded=$this->orient($decoded,$bytes,$image['mime']);
        $width=imagesx($decoded); $height=imagesy($decoded);
        // Re-encode the pixels: malformed images, embedded content and camera EXIF are not retained.
        ob_start();
        try {
            $saved=imagejpeg($decoded,null,88);
            $jpeg=ob_get_contents();
        } finally {
            ob_end_clean();
            imagedestroy($decoded);
        }
        if (!$saved || !$jpeg || strlen($jpeg)>2097152) $this->invalid();
        return ['bytes'=>$jpeg,'width'=>$width,'height'=>$height];
    }

    private function orient($image,$bytes,$mime)
    {
        if ($mime!=='image/jpeg' || !function_exists('exif_read_data')) return $image;
        $stream=fopen('php://temp','w+b');
        try {
            fwrite($stream,$bytes); rewind($stream);
            $exif=@exif_read_data($stream);
        } finally { fclose($stream); }
        $orientation=(int)($exif['Orientation']??1);
        $angle=[3=>180,5=>-90,6=>-90,7=>-90,8=>90][$orientation]??0;
        if ($angle) {
            $rotated=imagerotate($image,$angle,0);
            if (!$rotated) { imagedestroy($image); $this->invalid(); }
            imagedestroy($image); $image=$rotated;
        }
        if (in_array($orientation,[2,5],true)) imageflip($image,IMG_FLIP_HORIZONTAL);
        if (in_array($orientation,[4,7],true)) imageflip($image,IMG_FLIP_VERTICAL);
        return $image;
    }

    private function invalid()
    {
        throw ValidationException::withMessages(['reporter_photo_base64'=>'Capture a JPG, PNG or WebP photo up to 2 MB (maximum 4096 pixels per side).']);
    }

    public function store($image)
    {
        $path='sos-reporter-photos/'.bin2hex(random_bytes(16)).'.jpg';
        $disk=Storage::disk('local');
        try {
            if (!$disk->put($path,$image['bytes'])) throw new RuntimeException('Photo write failed.');
        } catch (\Throwable $e) {
            $disk->delete($path);
            throw new RuntimeException('Unable to save the SOS photo. Please retry.',0,$e);
        }
        return ['path'=>$path,'mime'=>'image/jpeg','width'=>$image['width'],'height'=>$image['height'],'stored_at'=>time()];
    }

    public function delete($photo)
    {
        if (!empty($photo['path'])) Storage::disk('local')->delete($photo['path']);
    }

    public function response($photo)
    {
        $path=$photo['path']??'';
        abort_unless(preg_match('#^sos-reporter-photos/[a-f0-9]{32}\.jpg$#',$path) && Storage::disk('local')->exists($path),404);
        return Storage::disk('local')->response($path,'sos-sender.jpg',[
            'Content-Type'=>'image/jpeg','Cache-Control'=>'private, no-store',
            'X-Content-Type-Options'=>'nosniff','Content-Security-Policy'=>"default-src 'none'",
        ]);
    }
}
