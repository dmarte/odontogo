<?php


namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UploadAvatar
{
    public function __invoke(Request $request, Model $model, $attribute, $requestAttribute, $disk, $storagePath): array
    {
        $file = $request->file($attribute);
        $path = $file->store("avatars");
        $realPath = storage_path("app/public/{$path}");

        $image = Image::make($file->getRealPath());
        $image->widen(1024, function ($constraint) {
            $constraint->upsize();
        });

        $image->save(storage_path("app/public/{$path}"));

        return [
            $attribute    => $path,
            'avatar_size' => Storage::size($path),
            'avatar_disk' => 'public',
        ];
    }
}
