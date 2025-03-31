<?php

namespace App\CPU;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ImageKit\ImageKit;
use Intervention\Image\Facades\Image;

class ImageManager
{
    public static function upload(string $folder_path, string $format, $file = null)
    {
        if ($file != null) {
            /* $imageName = Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->put($dir . $imageName, file_get_contents($image));
            $imageName = url('/').'/'.$imageName; */

            $file_name = $file->getClientOriginalName();
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $imageName = time() . '_' . uniqid() . '.' . $extension;

            if (env('DIR_PATH_FILE') == 'local') {
                try{
                    $file->move(public_path($folder_path), $imageName);
                    $result = url('/').'/'.$folder_path.'/'.$imageName;
                    return $result;
                }catch (\Exception $e) {
                    //return "Error: ".$e->getMessage();
                    return "Error33";
                }
            } else if(env('DIR_PATH_FILE') == 'imagekit'){
                try {
                    $imageKit = new ImageKit(
                        config('filesystems.imagekit.public_key'),
                        config('filesystems.imagekit.private_key'),
                        config('filesystems.imagekit.endpoint_url')
                    );
    
                    $contentoBinario = file_get_contents($file);
                    $imageBase64 = base64_encode($contentoBinario);
                    //Subiendo a imagekit
                    $upload_res = $imageKit->uploadFile([
                        'file' => $imageBase64, # required, "binary","base64" or "file url"
                        'fileName' => $imageName, # required
                        'folder' => $folder_path #folder to storage in imagekit
                    ]);
                    return $upload_res->result->fileId.','.$upload_res->result->url;
                } catch (\Exception $e) {
                    return "Error33";
                }
            }else{
                return "Error, no defina su ruta";
            }

        } else {
            $imageName = 'def.png';
        }
    }

    public static function update(string $dir, $old_image, string $format, $image = null)
    {
        if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = ImageManager::upload($dir, $format, $image);
        return url('/').'/'.$imageName;
    }

    public static function delete($path_url, $file_id=null)
    {
        /* if (Storage::disk('public')->exists($full_path)) {
            Storage::disk('public')->delete($full_path);
        } */
        if (strpos($path_url, 'https') !== false) {
            $imageKit = new ImageKit(
                config('filesystems.imagekit.public_key'),
                config('filesystems.imagekit.private_key'),
              config('filesystems.imagekit.endpoint_url')
            );

            try {
                $imageKit->deleteFile($file_id);
                //return "ok";
            } catch (\Exception $e) {
                return "falla";
            }
        } else {
            $host = url('/');
            $pathFile = substr($path_url, strlen($host)+1, strlen($path_url));
            if(File::exists(public_path($pathFile))){
                File::delete(public_path($pathFile));
                //return "ok";
            }
            return "falla";
        }

        return [
            'success' => 1,
            'message' => 'Removed successfully !'
        ];

    }
}
