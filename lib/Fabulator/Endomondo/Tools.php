<?php

namespace Fabulator\Endomondo;

use Fabulator\GoogleMaps\ElevationApi;

/**
 * Class Tools
 * @package Fabulator\Endomondo
 */
class Tools {

    /**
     * Tools constructor.
     */
    public function __construct() {}

    /**
     * Replace elevation data by Google maps elevation api.
     *
     * @param Workout $workout
     * @param ElevationApi $elevationApi
     * @return Workout
     */
    function rewriteElevationData(Workout $workout, ElevationApi $elevationApi)
    {
        $workout = clone $workout;

        // split points to chunks that can be requested in Google Elevation API
        $pointsChunks = array_chunk($workout->getPoints(), 200);

        /* @var $elevationData [[]] Elevation data from Google */
        $elevationData = [];

        foreach ($pointsChunks as $points) {
            $locations = [];
            foreach ($points as $point) {
                /* @var $point \Fabulator\Endomondo\Point */
                $locations[] = [
                    'lat' => $point->getLatitude(),
                    'lon' => $point->getLongitude(),
                ];
            }

            // request data from Google Elevation API and add them to results
            $elevationData = array_merge(
                $elevationData,
                $elevationApi->get($locations)['results']
            );
        }

        // Go throught Google Location find and find point in workout
        foreach($elevationData as $elevation) {
            foreach ($workout->getPoints() as $point) {
                if ((string) $point->getLatitude() == (string) $elevation['location']['lat'] && (string) $point->getLongitude()== (string) $elevation['location']['lng']) {
                    $point->setAltitude($elevation['elevation']);
                }
            }
        }

        return $workout;
    }

    /**
     * Recalculate ascent and descent based on altitude data.
     *
     * @param Workout $workout
     * @return Workout
     */
    function recalculateAscentDescent(Workout $workout)
    {
        $workout = clone $workout;

        $ascent = 0;
        $descent = 0;

        /* @var $lastPoint \Fabulator\Endomondo\Point */
        $lastPoint = null;
        foreach ($workout->getPoints() as $point) {
            if ($lastPoint === null) {
                $lastPoint = $point;
                continue;
            }

            if ($point->getAltitude() === null) {
                continue;
            }

            if ($point->getAltitude() > $lastPoint->getAltitude()) {
                $ascent += $point->getAltitude() - $lastPoint->getAltitude();
            } else if ($point->getAltitude() < $lastPoint->getAltitude()) {
                $descent += $lastPoint->getAltitude() - $point->getAltitude();
            }

            $lastPoint = $point;
        }

        $workout->setAscent($ascent);
        $workout->setDescent($descent);

        return $workout;
    }

}