<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet\Tests\controls;

use BeastBytes\Widgets\Leaflet\controls\ZoomControl;
use BeastBytes\Widgets\Leaflet\Map;
use PHPUnit\Framework\TestCase;

class ZoomControlTest extends TestCase
{
    public function test_zoom_control()
    {
        $control = new ZoomControl();

        $this->assertSame(
            Map::LEAFLET_VAR . ".control.zoom()",
            $control->toJs(Map::LEAFLET_VAR)
        );
    }
}
