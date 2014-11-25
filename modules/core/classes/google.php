<?php

class Google
{
    public static $results = NULL;

    public static $result = array();

    public static $strict = true;

    protected static $url = 'http://maps.googleapis.com/maps/api/geocode/json';

    public static function geocode($address)
    {
        $params = array(
            'address' => $address,
            'sensor' => 'false',
        );

        return static::runQuery($params);
    }

    public static function reverseGeocode($lat, $lng)
    {
        $params = array(
            'latlng' => $lat.','.$lng,
            'sensor' => 'false',
        );

        return static::runQuery($params);
    }


    public static function runQuery($params, $lat=NULL, $lng=NULL)
    {
        $response = Curl::get_contents(self::$url, $params, false);
        $response = (array)@json_decode($response, true);
        self::$results = (array)arr::get($response, 'results');

        // If no results return 0
        if ( ! count(self::$results)) {
            return FALSE;
        }


        foreach ((array)self::$results as $result) {
            $vp = self::getViewport((array)$result);
            if ( ! is_null($lat) and ! is_null($lng)) {
                // Check if given gps is inside the viewport
                if ($vp) {
                    if ($vp['southwest_lat'] < $lat
                        && $vp['southwest_lng'] < $lng
                        && $vp['northeast_lat'] > $lat
                        && $vp['northeast_lng'] > $lng) {
                        self::$result = $result;
                        break;
                    }
                }
            } else {
                // use first result and stop iteration
                self::$result = $result;
                break;
            }
        }

        return ( ! empty(self::$result));
    }


    public static function getLatitude()
    {
        $geometry = (array)arr::get((array)self::$result, 'geometry');
        $location = (array)arr::get($geometry, 'location');
        return arr::get($location, 'lat');
    }

    public static function getLongitude()
    {
        $geometry = (array)arr::get((array)self::$result, 'geometry');
        $location = (array)arr::get($geometry, 'location');
        return arr::get($location, 'lng');
    }

    public static function getLocationType1()
    {
        $result = self::$result;
        $addr = (array)arr::get((array)$result, 'address_components');
        $first = (array)arr::get($addr, 0);
        $types = (array)arr::get($first, 'types');
        return arr::get($types, 0, 'no_google_type');
    }

    public static function getLocationType2()
    {
        $result = self::$result;
        $addr = (array)arr::get((array)$result, 'address_components');
        $first = (array)arr::get($addr, 0);
        $types = (array)arr::get($first, 'types');
        return arr::get($types, 1, 'no_google_type');
    }


    public static function getViewport($result=NULL)
    {
        if (empty($result)) {
            $result = (array)self::$result;
        }
        $addr = (array)arr::get($result, 'geometry');
        $first = (array)arr::get($addr, 'viewport');
        $northeast = (array)arr::get($first, 'northeast');
        $southwest = (array)arr::get($first, 'southwest');

        if ( ! isset($northeast['lat']) or $northeast['lat'] == '') {
            return array();
        }

        return array(
            'northeast_lat' => arr::get($northeast, 'lat'),
            'northeast_lng' => arr::get($northeast, 'lng'),
            'southwest_lat' => arr::get($southwest, 'lat'),
            'southwest_lng' => arr::get($southwest, 'lng'),
        );
    }

    public static function getFormattedAddress()
    {
        return arr::get(self::$result, 'formatted_address');
    }



}
