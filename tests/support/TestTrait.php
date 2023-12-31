<?php
/**
 * @copyright Copyright © 2023 BeastBytes - All rights reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet\Tests\support;

use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Cache;
use Yiisoft\Cache\CacheInterface;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\Test\Support\SimpleCache\MemorySimpleCache;
use Yiisoft\View\WebView;
use Yiisoft\Widget\WidgetFactory;

trait TestTrait
{
    private CacheInterface $cache;
    private WebView $webView;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new SimpleContainer(
            [
                Aliases::class => new Aliases(['@public' => __DIR__]),
                CacheInterface::class => new Cache(new MemorySimpleCache()),
                WebView::class => new WebView(__DIR__ . '/public/view', new SimpleEventDispatcher()),
            ]
        );

        WidgetFactory::initialize($container, []);

        $this->cache = $container->get(CacheInterface::class);
        $this->webView = $container
            ->get(WebView::class)
            ->withBasePath(__DIR__ . '/view')
        ;
    }
}
