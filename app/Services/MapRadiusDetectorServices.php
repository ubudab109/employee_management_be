<?php

namespace App\Services;

class MapRadiusDetectorServices
{

    /**
     * DETECT RADIUS USER DEVICE WITH BRANCH RADIUS
     * @param double $userLat - Latitude user devices
     * @param double $userLong - Longitude user devices
     * @param double $branchLat - Company branch latitude
     * @param double $branchLong - Company branch longitude
     * @return object
     */
    public static function detect($userLat, $userLong, $branchLat, $branchLong)
    {   
        /* DEVICE LATITUDE AND LONGITUDE */
        $myLat = $userLat;
        $myLong = $userLong;
        
        /* BRANCH LATITUDE AND LONGITUDE */
        $lat = $branchLat;
        $lon = $branchLong;
        $distance = self::getDistanceBetweenPointsNew($myLat, $myLong, $lat, $lon, 'kilometers');
        if ($distance > 1) {
            return [
                'status'  => false,
                'message' => 'Location is to far'
            ];
        }
        return [
            'status'  => true,
            'message' => 'Location match'
        ];
    }

    /**
     * It converts the latitude and longitude of two locations to radians, calculates the great circle
     * distance between them using the Haversine formula, converts the distance from radians to the
     * desired unit, and rounds the result to the specified number of decimal places
     * 
     * @param double $latitude1 The latitude of the first point
     * @param double $longitude1 The longitude of the first point
     * @param double $latitude2 The latitude of the point you want to find the distance to.
     * @param double $longitude2 -122.4194155
     * @param string $unit miles or kilometers
     * 
     * @return double The distance between two points.
     */
    private static function getDistanceBetweenPointsNew($latitude1, $longitude1, $latitude2, $longitude2, $unit = 'miles') 
    {
        $theta = $longitude1 - $longitude2; 
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta))); 
        $distance = acos($distance); 
        $distance = rad2deg($distance); 
        $distance = $distance * 60 * 1.1515; 
        switch($unit) { 
            case 'miles': 
            break; 
            case 'kilometers' : 
            $distance = $distance * 1.609344; 
        } 
        return (round($distance,2)); 
    }
}