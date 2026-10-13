<?php
/** Each smoke test gets a disposable private disk, never the installed photo directory. */
function sosPhotoFixtureDisk()
{
    $previous=config('filesystems.disks.local.root');
    $root=storage_path('framework/testing/sos-photo-'.bin2hex(random_bytes(8)));
    if (!is_dir($root)) mkdir($root,0777,true);
    config(['filesystems.disks.local.root'=>$root]);
    Illuminate\Support\Facades\Storage::forgetDisk('local');
    return function () use ($previous,$root) {
        $disk=Illuminate\Support\Facades\Storage::disk('local');
        $disk->delete($disk->allFiles());
        foreach (array_reverse($disk->allDirectories()) as $directory) $disk->deleteDirectory($directory);
        @rmdir($root);
        config(['filesystems.disks.local.root'=>$previous]);
        Illuminate\Support\Facades\Storage::forgetDisk('local');
    };
}

function sosPhotoFixtureBase64()
{
    $image=imagecreatetruecolor(64,48);
    imagefill($image,0,0,imagecolorallocate($image,32,112,96));
    ob_start(); imagejpeg($image,null,85); $bytes=ob_get_clean(); imagedestroy($image);
    return base64_encode($bytes);
}

function sosVoiceFixtureInput()
{
    $bytes=file_get_contents(__DIR__.'/fixtures/sos-voice-10s.m4a');
    if (!$bytes) throw new RuntimeException('The isolated SOS voice fixture is missing.');
    return ['reporter_voice_base64'=>base64_encode($bytes),'reporter_voice_duration_ms'=>10000];
}
