<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet\Tests\layers\raster;

use BeastBytes\Widgets\Leaflet\layers\raster\TileLayer;
use BeastBytes\Widgets\Leaflet\Map;
use PHPUnit\Framework\TestCase;

class TileLayerTest extends TestCase
{
    public function test_tile_layer()
    {
        $url = 'https://example.com/tiles';
        $minZoom = 5;

        $tileLayer = new TileLayer($url, ['minZoom' => $minZoom]);

        $this->assertSame(
            Map::LEAFLET_VAR . ".tileLayer(\"$url\",{minZoom:$minZoom})",
            $tileLayer->toJs(Map::LEAFLET_VAR)
        );
    }
}
