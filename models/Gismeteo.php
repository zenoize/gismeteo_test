<?php

namespace app\models;

class Gismeteo
{
    const API_TOKEN = '5c10e9ce8e02f6.83138424';

    public static function getTemperatureByIP($ip)
    {
        $cityId = static::getCityIdByIP($ip);

        if ($cityId === null) return false;

        return static::gettemperatureByCityId($cityId);
    }

    private static function gettemperatureByCityId($id)
    {
        $endpoint = 'https://api.gismeteo.net/v2/weather/current/' . $id;
        $weather = static::makeRequest($endpoint);

        return isset($weather['response']['temperature']) ? $weather['response']['temperature'] : false;
    }

    public static function getTemperatureOnWeekendsByCoors($lat, $lon)
    {
        $endpoint = 'https://api.gismeteo.net/v2/weather/forecast/aggregate/';
        $params = [
            'latitude' => $lat,
            'longitude' => $lon,
            'days' => 7,
        ];

        $weekends = [];
        $result = static::makeRequest($endpoint, $params);
        if (!isset($result['response'])) return false;

        foreach ($result['response'] as $item) {
            $dayOfWeek = date('w', $item['date']['unix']);
            if (in_array($dayOfWeek, [5,6,0])) {
                $weekends[$item['date']['local']] = $item['temperature'];
            }
        }

        return $weekends;
    }

    public static function getTemperatureInNearestPlacesByCoords($lat, $lon)
    {
        $cities = static::getCitiesByCoords($lat, $lon, 3);
        $result = [];

        if (!isset($cities['response'])) return false;

        foreach ($cities['response'] as $city) {
            if (isset($city['id'])) {
                $result[$city['name']] = static::gettemperatureByCityId($city['id']);
            }
        }

        return $result;
    }
    
    private static function getCityIdByIP($ip)
    {
        $endpoint = 'https://api.gismeteo.net/v2/search/cities/';
        $result = static::makeRequest($endpoint, ['ip' => $ip]);

        return isset($result['response']['id']) ? $result['response']['id'] : null;
    }

    private static function getCitiesByCoords($lat, $lon, $limit)
    {
        $endpoint = 'https://api.gismeteo.net/v2/search/cities/';
        $params = [
            'latitude' => $lat,
            'longitude' => $lon,
            'limit' => $limit,
        ];

        $result = static::makeRequest($endpoint, $params);

        return $result;
    }

    private static function makeRequest($endpoint, $params = [])
    {
        try {
            $client = new \GuzzleHttp\Client();
            $res = $client->request('GET', $endpoint, [
                'headers' => [
                    'X-Gismeteo-Token' => static::API_TOKEN,
                    'Accept-Encoding' => 'gzip',
                ],
                'query' => $params,
                'decode_content' => 'gzip',
            ]);
            $result = json_decode($res->getBody()->getContents(), true);
        } catch (\Exception $exception) {
            $result = [];
        }

        return $result;
    }
}