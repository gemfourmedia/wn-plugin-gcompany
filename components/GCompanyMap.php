<?php namespace GemFourMedia\GCompany\Components;

use GemFourMedia\GCompany\Classes\GCompanyComponent;
use GemFourMedia\GCompany\Models\Address;
use GemFourMedia\GCompany\Models\Setting;

class GCompanyMap extends GCompanyComponent
{
    public $address;
    public $map;
    

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.map.name',
            'description' => 'gemfourmedia.gcompany::lang.components.map.desc',
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'mode' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.mode',
                'type' => 'dropdown',
                'default' => 'address',
                'options' => [
                    'address' => 'gemfourmedia.gcompany::lang.components.map.props.mode_address',
                    'custom' => 'gemfourmedia.gcompany::lang.components.map.props.mode_custom',
                ],
                'showExternalParam' => false,
            ],
            'addressFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.addressFilter',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.addressFilter_desc',
                'type' => 'dropdown',
                'default' => 'all',
                'showExternalParam' => false,
            ],
            'mapType' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.mapType',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.mapType_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'dropdown',
                'default' => 'roadmap',
                'options' => [
                    'hybrid' => 'gemfourmedia.gcompany::lang.components.map.props.mapType_hybrid',
                    'roadmap' => 'gemfourmedia.gcompany::lang.components.map.props.mapType_roadmap',
                    'satellite' => 'gemfourmedia.gcompany::lang.components.map.props.mapType_satellite',
                    'terrain' => 'gemfourmedia.gcompany::lang.components.map.props.mapType_terrain',
                ],
            ],
            'mapCenterType' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.mapCenterType',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.mapCenterType_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'dropdown',
                'default' => 'coordinate',
                'options' => [
                    'coordinate' => 'gemfourmedia.gcompany::lang.components.map.props.mapCenterType_coordinate',
                ],
            ],
            'mapCenterCoordinate' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.mapCenterCoordinate',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.mapCenterCoordinate_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'string',
                'default' => '',
            ],
            'width' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.width',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.width_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'string',
                'default' => 'auto',
            ],
            'height' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.height',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.height_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'string',
                'default' => '350',
            ],
            'zoom' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.zoom',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.zoom_desc',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Number only',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'string',
                'default' => 16,
            ],
            'zoomControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.zoomControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.zoomControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 1,
            ],
            'panControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.panControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.panControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 1,
            ],
            'mapTypeControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.mapTypeControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.mapTypeControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'scaleControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.scaleControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.scaleControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'overviewMapControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.overviewMapControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.overviewMapControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'streetViewControl' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.streetViewControl',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.streetViewControl_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'draggable' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.draggable',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.draggable_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 1,
            ],
            'disableDoubleClickZoom' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.disableDoubleClickZoom',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.disableDoubleClickZoom_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
            'scrollwheel' => [
                'title' => 'gemfourmedia.gcompany::lang.components.map.props.scrollwheel',
                'description' => 'gemfourmedia.gcompany::lang.components.map.props.scrollwheel_desc',
                'group' => 'gemfourmedia.gcompany::lang.components.map.props.group_map',
                'type' => 'checkbox',
                'default' => 0,
            ],
        ];
    }

    public function getAddressFilterOptions()
    {
        return [''=>'All'] + Address::lists('name', 'id');
    }

    public function onRun()
    {
        $this->prepareVars();
    }

    public function onRender()
    {
        if (!$this->map) {
            $this->prepareVars();
        }
    }

    public function prepareVars()
    {
        $this->address = $this->page['address'] = $this->loadAddress();

        $this->map = $this->page['map'] = [
            'setting' => $this->prepareMapSetting(),
            'map_api' => '//maps.google.com/maps/api/js?language='.\App::getLocale().'&key='.Setting::get('google_maps_key'),
            'markers' => base64_encode(json_encode($this->prepareMapMarkers())),
        ];
    }

    protected function prepareMapSetting() {
        $mapSettings =  [
            'mapType' => $this->property('mapType'),
            'width' => $this->property('width', 'auto'),
            'height' => $this->property('height', '350px'),
            'zoom' => $this->property('zoom'),
            'zoomControl' => $this->property('zoomControl'),
            'scaleControl' => $this->property('scaleControl'),
            'panControl' => $this->property('panControl'),
            'mapTypeControl' => $this->property('mapTypeControl'),
            'streetViewControl' => $this->property('streetViewControl'),
            'overviewMapControl' => $this->property('overviewMapControl'),
            'draggable' => $this->property('draggable'),
            'disableDoubleClickZoom' => $this->property('disableDoubleClickZoom'),
            'scrollwheel' => $this->property('scrollwheel'),
            'mapCenterType' => 'coordinate',
            'mapCenterAddress' => $this->property('mapCenterAddress', null),
            'mapCenterCoordinate' => $this->prepareMapCenter(),
        ];

        return $mapSettings;
    }

    protected function prepareMapCenter()
    {
        // Custom coordinate
        if ( $this->property('mode') == 'custom') return $this->property('mapCenterCoordinate');
        
        // Filtered Address coordinate
        if ($this->address) return $this->address->map_coordinate;

        // Default Address coordinate
        $address = Address::isDefault()->first();
        if ($address) return $address->map_coordinate;

    }

    protected function prepareMapMarkers()
    {
        if ( $this->property('mode') == 'custom') return [];

        // Filtered Address coordinate
        if ($this->address) return [$this->address->map_marker];
        
        // All Markers
        return Address::all()->pluck('map_marker');
    }

    public function loadAddress()
    {
        if (!$id = $this->property('addressFilter')) return null;
        $address = Address::find($id);

        return $address ?? null;
    }
}
