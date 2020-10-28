<?php


namespace Paul\Links;


class GeoInformer
{
    const GEO_URL_SERVICE = 'http://ipwhois.app/json/';

    /**
     * Get geo data by ip address
     *
     * @param $ip
     * @return mixed
     */
    public static function getGeoDataByIP($ip = null)
    {
        $curl = curl_init(self::GEO_URL_SERVICE . $ip ?? $_SERVER['REMOTE_ADDR']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $jsonResult = curl_exec($curl);
        curl_close($curl);

        // Decode JSON response
        return json_decode($jsonResult, true);
    }
}