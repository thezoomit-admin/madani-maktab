<?php 
namespace App\Helpers;

use App\Models\UserReporting;

class ReportingService
{ 
    public static function getAllJunior($userId, $allData = null, &$collectedJuniorIds = [])
    { 
        static $cachedData;
        $cachedData = $cachedData ?? UserReporting::whereNull('end_date')->get()->groupBy('reporting_user_id');
        $allData = $allData ?? $cachedData;

        // Get juniors for the given user
        $juniors = $allData->get($userId, collect())->pluck('user_id')->toArray();  
        foreach ($juniors as $juniorId) {
            if (!in_array($juniorId, $collectedJuniorIds)) {
                $collectedJuniorIds[] = $juniorId;
                self::getAllJunior($juniorId, $allData, $collectedJuniorIds);
            }
        } 
        return $collectedJuniorIds;
    }


    public static function getAllSenior($userId, $allData = null, &$collectedSeniorIds = [])
    { 
        static $cachedData;
        $cachedData = $cachedData ?? UserReporting::whereNull('end_date')->get()->keyBy('user_id'); 
        $allData = $allData ?? $cachedData;
    
        while (isset($allData[$userId])) {
            $senior = $allData[$userId];
            $userId = $senior->reporting_user_id;

            if (!in_array($userId, $collectedSeniorIds)) {
                $collectedSeniorIds[] = $userId;
            }
        } 
        return $collectedSeniorIds;
    }


    

}
