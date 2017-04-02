<?php

namespace Fabulator\GoogleMaps;

use GuzzleHttp\Client;

/**
 * Class ElevationApi
 * @package Fabulator\GoogleMaps
 */
class ElevationApi {

    /**
     * @var string
     */
    private $key;

    /**
     * @var Client
     */
    private $client;

    /**
     * ElevationApi constructor.
     * @param $key string
     * @param string $format
     */
    public function __construct($key, $format = 'json')
    {
        $this->key = $key;
        $this->client = new Client([
            'base_uri' => 'https://maps.googleapis.com/maps/api/elevation/' . $format,
        ]);
    }

    /**
     * @param $points array
     * @return array
     */
    public function get($points)
    {
        $locations = [];

        foreach ($points as $point) {
            $locations[] = $point['lat'] . ',' . $point['lon'];
        }

        $response = $this->client->get('?locations=' . join('|', $locations) . '&key=' . $this->key);

        return json_decode((string) $response->getBody(), true);
    }

}