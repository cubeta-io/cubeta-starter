<?php

namespace Cubeta\CubetaStarter\Traits;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


trait FileHandler
{
    private $files;

    /**
     * this function takes a base64 encoded image and store it in the filesystem and return the name of it
     * (ex. 12546735.png) that will be stored in DB
     * @param $file
     * @param $dir
     * @param bool $to_compress
     * @param false $is_base_64
     * @param int $width
     * @return string
     */
    public function storeFile($file, $dir,$to_compress = true,$is_base_64=false,$width = 300)
    {
        $this->files = new Filesystem();
        $this->makeDirectory(storage_path('app/public/'.$dir));
        if($is_base_64)
            $name = $dir . '/' . str_replace([':', '\\', '/', '*'], '', bcrypt(microtime(true))) . '.' . explode('/', explode(':', explode(';', $file)[0])[1])[1];
        else
            $name = $dir . '/' . $file->hashName();
        if($to_compress)
            Image::make($file)->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save(storage_path('app/public/') . $name);
        else
            Image::make($file)->save(storage_path('app/public/') . $name);
        return $name;
    }

    /**
     * this function takes $newImage(base64 encoded) and $oldImage(DB name) ,
     * it deletes the $oldImage from the filesystem and store the $newImage and return it's name that will be stored in DB
     * @param $new_file
     * @param $old_file
     * @param $dir
     * @param bool $to_compress
     * @param bool $is_base_64
     * @param int $width
     * @return string
     */
    public function updateFile($new_file, $old_file, $dir,$to_compress = true,$is_base_64=false,$width = 300)
    {
        if($old_file)
            $this->deleteFile($old_file);
        return $this->storeFile($new_file,$dir,$to_compress,$is_base_64,$width);
    }

    /**
     * this function takes image(DB name) and deletes it from the filesystem ,
     * returns true if deleted and false if not found
     * @param $file
     * @return bool
     */
    public function deleteFile($file)
    {
        if(file_exists(storage_path('app/public/').$file)){
            Storage::disk('public')->delete($file);
            return true;
        }
        return false;
    }

    /**
     * this function can store any file
     * @param string $key key as sent in the request
     * @return string
     */
    public function storeNormalFile($key)
    {
        // Get filename with the extension
        $filenameWithExt = request()->file($key)->getClientOriginalName();
        //Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        // Get just ext
        $extension = request()->file($key)->getClientOriginalExtension();
        // Filename to store
        $fileNameToStore = 'files/'.$filename.'_'.time().'.'.$extension;
        // Upload Image
        $path = request()->file($key)->storeAs('public/',$fileNameToStore);

        return $fileNameToStore;
    }

    /**
     * make directory for files
     * @param $path
     * @return mixed
     */
    private function makeDirectory($path)
    {
        $this->files->makeDirectory($path, 0777, true,true);
        return $path;
    }

    /**
     * store requested keys as files
     * @param array $data
     * @param array $filesKeys
     * @param bool $is_store
     * @param null $item
     * @param bool $to_compress
     * @param false $is_base_64
     * @param int $width
     * @return array
     */
    private function storeOrUpdateRequestedFiles(array $data , array $filesKeys = [] , $is_store = true, $item = null, $to_compress = true,$is_base_64=false,$width = 300){
        $model_files = [];
        if(count($filesKeys) > 0 ){
            foreach ($filesKeys as $file){
                if(in_array($file,$data)) {
                    if ($is_store)
                        $model_files["$file"] = $this->storeFile($data["$file"], $this->model->getTable(), $to_compress, $is_base_64, $width);
                    else
                        $model_files["$file"] = $this->updateFile($data["$file"], $item->{"$file"}, $this->model->getTable(), $to_compress, $is_base_64, $width);
                    unset($data["$file"]);
                }
            }
            $data = array_merge($data , $model_files);
        }
        return $data;
    }
}

