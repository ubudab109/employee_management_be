<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MapServices
{
    public $lat, $long, $apiKey;

    public function __construct($lat, $long)
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->apiKey = config('googlemaps.google_maps_api_key');
    }

    public function getAddressByCoordinate()
    {
        $maps = Http::get('https://maps.googleapis.com/maps/api/geocode/json?key='.$this->apiKey.'&latlng='.$this->lat.','.$this->long.'&sensor=false');

        return $maps;
    }
}