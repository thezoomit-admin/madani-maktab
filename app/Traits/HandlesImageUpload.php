<?php

namespace App\Traits;

use App\Models\HijriMonth;
use Illuminate\Http\Request;

trait HandlesImageUpload
{
    /**
     * Upload image file with Hijri year-based folder structure
     * 
     * @param Request $request
     * @param string $inputName The name of the file input field
     * @param string $folder The base folder path (e.g., 'uploads/profile_images')
     * @return string|null Returns relative path or null if no file uploaded
     */
    protected function uploadImage(Request $request, string $inputName, string $folder): ?string
    {
        if (!$request->hasFile($inputName)) {
            return null;
        }

        $image = $request->file($inputName);
        $imageName = time() . '_' . $image->getClientOriginalName();
        
        // Get Hijri active year, fallback to English year if not available
        $activeMonth = HijriMonth::getActiveMonth();
        $year = $activeMonth ? $activeMonth->year : date('Y');
        
        // Extract base folder (e.g., 'uploads' from 'uploads/profile_images')
        $baseFolder = 'uploads';
        $subFolder = str_replace($baseFolder . '/', '', $folder);
        
        // Path structure: uploads/{year}/{subfolder}/
        $uploadPath = public_path($baseFolder . '/' . $year . '/' . $subFolder);

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $image->move($uploadPath, $imageName);
        
        // Return path: uploads/{year}/{subfolder}/{filename}
        return $baseFolder . '/' . $year . '/' . $subFolder . '/' . $imageName;
    }

    /**
     * Get current Hijri year for folder organization
     * 
     * @return string Hijri year or English year as fallback
     */
    protected function getCurrentYear(): string
    {
        $activeMonth = HijriMonth::getActiveMonth();
        return $activeMonth ? $activeMonth->year : date('Y');
    }
}

