<?php

namespace App\Http\Traits;

use App\Http\Enums\RequestTypes;
use App\Models\Hotelbeds_credential;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait HBFileStoreTrait
{
    public function cpUploadFileToStorage($pFile, $pFileStoreLocation = "Images")
    {
        // Get just ext
        $mExtension = $pFile->getClientOriginalExtension();
        //Filename to store
        $mFileNameToStore = 'img_' . time() . '.' . $mExtension;
        // Upload Image
        $mPath = $pFile->storeAs('public/' . $pFileStoreLocation, $mFileNameToStore);
        return $mPath;
    }

    public function cpUploadFileToS3($pFile, $pFileStoreLocation = "photos")
    {
    }
}
