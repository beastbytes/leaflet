<?php
/**
 * @copyright Copyright (c) 2022 BeastBytes - All Rights Reserved
 * @license BSD 3-Clause
 */

declare(strict_types=1);

namespace BeastBytes\Widgets\Leaflet;

use BeastBytes\Widgets\Leaflet\controls\Control;
use BeastBytes\Widgets\Leaflet\layers\Layer;
use BeastBytes\Widgets\Leaflet\types\LatLng;
use BeastBytes\Widgets\Leaflet\types\LatLngBounds;
use JsonException;
use Yiisoft\Definitions\Exception\InvalidConfigException;
use Yiisoft\Html\Html;
use Yiisoft\View\WebView;
use Yiisoft\Widget\Widget;

/**
 * A leaflet map
 */
final class Map extends Widget
{
    use EventsTrait;
    use OptionsTrait;

    public const ID_PREFIX = 'map';
    public const LEAFLET_VAR = 'L';
    private const COMPONENT_TYPE_CONTROLS = 'controls';
    private const COMPONENT_TYPE_LAYERS = 'layers';
    private const COMPONENT_TYPE_PLUGINS = 'plugins';
    private const COMPONENT_TYPES = [
        self::COMPONENT_TYPE_LAYERS, // self::COMPONENT_TYPE_LAYERS must be first
        self::COMPONENT_TYPE_CONTROLS,
        self::COMPONENT_TYPE_PLUGINS,
    ];

    /**
     * @var array $options
     * @see $layers for how to specify the layers option
     * @link https://leafletjs.com/reference.html#map-factory
     */

    /**
     * @var array<string, string> HTML attributes for the container tag
     * The `style` attribute must be set and specify a height
     */
    private array $attributes = [];
    /**
     * @var array Map controls
     */
    private array $controls = [];
    /**
     * @var array Map layers
     *
     * If a LayersControl is being added to the map use ["Layer Label" => Layer object] (the same applies to
     * $options['layers']) then provide the layer labels to LayersControl::baseLayers and LayersControl::overlays
     */
    private array $layers = [];
    /**
     * @var string The variable used by Leaflet.
     * If this is not the default `L` the noConflict() method is called and the new variable used for Leaflet.
     * **WARNING:** _Some plugins require the default variable name for Leaflet_
     */
    private string $leafletVar = self::LEAFLET_VAR;

    /**
     * @var array Plugins
     */
    private array $plugins = [];

    /**
     * @psalm-var non-empty-string $tag
     */
    private string $tag = 'div';

    /**
     * @var array<array-key, string> Map JavaScript
     */
    private array $js = [];

    /**
     * @var array<Layer> Layers initially added to the map
     */
    private array $mapLayers = [];

    public function __construct(private WebView $webView)
    {
    }

    public function controls(Control ...$controls): self
    {
        $new = clone $this;

        foreach ($controls as $control) {
            $name = strtolower(substr($control::class, strrpos($control::class, '\\') + 1));
            $new->controls[$name] = $control;
        }

        return $new;
    }

    /**
     * @param array<string, Layer> $layers
     */
    public function addLayers(array $layers): self
    {
        $new = clone $this;
        $new->layers = array_merge($new->layers, $layers);

        return $new;
    }

    public function addPlugins(Component ...$plugins): self
    {
        $new = clone $this;

        foreach ($plugins as $plugin) {
            $name = strtolower(substr($plugin::class, strrpos($plugin::class, '\\') + 1));
            $new->plugins[$name] = $plugin;
        }

        return $new;
    }

    /**
     * Returns a new instance with the HTML attributes.
     *
     * @param array $valuesMap Attribute values indexed by attribute names.
     * @return $this
     */
    public function attributes(array $valuesMap): self
    {
        $new = clone $this;
        $new->attributes = $valuesMap;

        return $new;
    }

    public function leafletVar(string $leafletVar): self
    {
        $new = clone $this;
        $new->leafletVar = $leafletVar;

        return $new;
    }

    /**
     * @param array $valuesMap Map options
     * @return $this
     */
    public function options(array $valuesMap): self
    {
        $new = clone $this;
        $new->options = $valuesMap;

        return $new;
    }

    /**
     * @psalm-param non-empty-string $tag HTML tag for the map - defaults to 'div'
     * @return $this
     */
    public function tag(string $tag): self
    {
        $new = clone $this;
        $new->tag = $tag;

        return $new;
    }

    /**
     * @throws InvalidConfigException|JsonException
     */
    public function render(): string
    {
        if (!isset($this->options['center'])) {
            throw new InvalidConfigException("`options['center']` must be set");
        }

        if (!isset($this->options['zoom'])) {
            throw new InvalidConfigException("`options['zoom']` must be set");
        }

        if (
            !isset($this->attributes['style'])
            || preg_match('/height:.*;/', $this->attributes['style']) === 0
        ) {
            throw new InvalidConfigException(
                "`attributes['style']` must be set and define the height of the map"
            );
        }

        if (is_array($this->options['center'])) {
            $this->options['center'] = new LatLng($this->options['center']);
        }

        if (isset($this->options['maxBounds']) && is_array($this->options['maxBounds'])) {
            $this->options['maxBounds'] = new LatLngBounds(
                $this->options['maxBounds'][0],
                $this->options['maxBounds'][1]
            );
        }

        if (!array_key_exists('id', $this->attributes)) {
            $this->attributes['id'] = Html::generateId(self::ID_PREFIX);
        }

        $this->registerJS();
        return Html::tag($this->tag, '', $this->attributes)->render();
    }

    /**
     * Registers the map JavaScript with the view
     *
     * @throws JsonException
     */
    public function registerJs(): void
    {
        $id = $this->attributes['id'];

        if ($this->leafletVar !== self::LEAFLET_VAR) {
            array_unshift(
                $this->js,
                "const $this->leafletVar=" . self::LEAFLET_VAR . '.noConflict();'
            );
        }

        // Generate code for layers defined in the map
        if (isset($this->options['layers'])) {
            /** @var int $key */
            /** @var Layer $layer */
            foreach ($this->options['layers'] as $key => $layer) {
                $layer = $layer->addToMap(false);
                $jsVar = $layer->getJsVar();
                $this->mapLayers[$key] = '!!'. $jsVar . '!!'; // !! <> !! mark it as a JS variable
                $this->js[] = "const $jsVar={$layer->toJs($this->leafletVar)}";
            }

            $this->options['layers'] = array_values($this->mapLayers);
        }

        $this->js[] = "const $id=$this->leafletVar.map(\"$id\",{$this->options2Js($this->leafletVar)})"
            . $this->events2Js()
        ;

        $this->components2Js();

        $this->webView->registerJs(implode(';', $this->js) . ';');
    }

    /**
     * Generates JavaScript for map components - layers, controls, and plugins
     *
     * @throws JsonException
     */
    private function components2Js(): void
    {
        foreach (self::COMPONENT_TYPES as $componentType) {
            /**
             * @var Component $component
             * @var string $key
             */
            foreach ($this->$componentType as $key => $component) {
                if ($componentType === self::COMPONENT_TYPE_LAYERS) {
                    $this->layers[$key] = $component->getJsVar();
                }

                if ($component instanceof LayersInterface) {
                    $this->setComponentLayers($component);
                }

                $js = "const {$component->getJsVar()}={$component->toJs($this->leafletVar)}";
                $js .= $component->events2Js();
                $js .= ($component->getAddToMap() ? ".addTo({$this->attributes['id']})" : '');
                $this->js[] = $js;
            }
        }
    }

    private function setComponentLayers(LayersInterface $component): void
    {
        $baseLayers = $overlays = [];

        foreach ($component->getBaseLayers() as $label) {
            if (isset($this->layers[$label])) {
                $baseLayers[$label] = $this->layers[$label];
            } elseif (isset($this->mapLayers[$label])) {
                $baseLayers[$label] = $this->mapLayers[$label];
            }
        }

        foreach ($component->getOverlays() as $label) {
            if (isset($this->layers[$label])) {
                $overlays[$label] = $this->layers[$label];
            } elseif (isset($this->mapLayers[$label])) {
                $overlays[$label] = $this->mapLayers[$label];
            }
        }

        // set the layers in the component; each layer is "label"=>layerJsVar
        $component->setBaseLayers($baseLayers);
        $component->setOverlays($overlays);
    }
}
