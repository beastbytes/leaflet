<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet\Tests\layers\raster;

use BeastBytes\Widgets\Leaflet\layers\raster\VideoOverlay;
use BeastBytes\Widgets\Leaflet\Map;
use BeastBytes\Widgets\Leaflet\types\LatLng;
use BeastBytes\Widgets\Leaflet\types\LatLngBounds;
use PHPUnit\Framework\TestCase;

class VideoOverlayTest extends TestCase
{
    public function test_video_overlay()
    {
        $url = 'https://example.com/videooverlay.webm';
        $lat1 = random_int(-9000, 9000) / 100;
        $lng1 = random_int(-18000, 18000) / 100;
        $lat2 = random_int(-9000, 9000) / 100;
        $lng2 = random_int(-18000, 18000) / 100;
        $latLng1 = new LatLng($lat1, $lng1);
        $latLng2 = new LatLng($lat2, $lng2);

        $latLngBounds = new LatLngBounds($latLng1, $latLng2);
        $videoOverlay = new VideoOverlay($url, $latLngBounds, ['alt' => 'videoOverlay']);

        $this->assertSame(
            Map::LEAFLET_VAR . ".videoOverlay(\"$url\","
            . Map::LEAFLET_VAR . ".latLngBounds("
                . Map::LEAFLET_VAR . ".latLng($lat1,$lng1),"
                . Map::LEAFLET_VAR . ".latLng($lat2,$lng2)"
            . "),"
            . '{alt:"videoOverlay"})',
            $videoOverlay->toJs(Map::LEAFLET_VAR)
        );
    }
}
