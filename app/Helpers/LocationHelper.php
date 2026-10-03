<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Cache;

class LocationHelper
{
    public static function getAddress($lat, $lng)
    {
        if (!$lat || !$lng) return null;

        $cacheKey = "address_" . md5($lat . '_' . $lng);

        // Return cached if exists
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lng}&addressdetails=1";
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: AkunthaApp/1.0\r\n"
            ]
        ];
        $context = stream_context_create($opts);

        try {
            $response = @file_get_contents($url, false, $context);
            if (!$response) return null; // don't cache failed requests

            $json = json_decode($response);
            $address = $json->display_name ?? null;

            // ✅ Only cache if we got a valid address
            if ($address) {
                Cache::put($cacheKey, $address, now()->addDays(30));
            }

            return $address;
        } catch (\Exception $e) {
            return null; // don't cache exceptions
        }
    }
}