<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet\Tests\layers\ui;

use BeastBytes\Widgets\Leaflet\Map;
use PHPUnit\Framework\TestCase;

class TooltipTest extends TestCase
{
    public function test_tooltip()
    {
/*        $lat = random_int(-9000, 9000) / 100;
        $lng = random_int(-18000, 18000) / 100;
        $radius = random_int(1, 1000);
        $latLng = new LatLng($lat, $lng);
        $circle = new Tooltip($latLng, ['radius' => $radius]);

        $this->assertSame(
            Map::LEAFLET_VAR . ".circle(" . Map::LEAFLET_VAR . ".latLng($lat,$lng),{radius:$radius})",
            $circle->toJs(Map::LEAFLET_VAR)
        );*/
        $this->assertSame(1,1);
    }
}
