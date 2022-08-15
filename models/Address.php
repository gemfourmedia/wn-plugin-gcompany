<?php namespace GemFourMedia\GCompany\Models;

use Model;
use DB;
use GemFourMedia\GCompany\Models\Setting;

/**
 * Model
 */
class Address extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \GemFourMedia\GCompany\Traits\SEOHelper;
    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_addresses';

    protected $appends = ['full_address', 'short_address', 'main_image', 'map_coordinate', 'map_marker', 'direction_link'];

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'main_image';
    
    /**
     * @var string name of og:type
     */
    public $ogType = 'website';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'code' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcompany_addresses'],
        'address_1' => 'max:255',
        'address_2' => 'max:255',
        'type' => 'max:30',
        'city' => 'max:150',
        'state' => 'max:150',
        'country' => 'max:255',
        'zip' => 'max:30',
        'phone' => 'max:255',
        'email' => 'email|max:255',
        'lat' => 'max:15',
        'lng' => 'max:15',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
        'is_default' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'address_1',
        'address_2',
        'desc',
        'city',
        'state',
        'country',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['code', 'index' => true]
    ];

    /*
     * Relationships
     * ===
     */
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function (self $address) {
            $address->handleDefaultStatus();
        });
    }

    /**
     * Makes sure that there is only one default address
     */
    protected function handleDefaultStatus()
    {
        $existingAddresses = DB::table('gemfourmedia_gcompany_addresses')->count();

        if ($existingAddresses === 0) {
            return $this->is_default = true;
        }

        if ($this->is_default) {
            DB::table('gemfourmedia_gcompany_addresses')
              ->where('id', '<>', $this->id)
              ->update(['is_default' => false]);
        }
    }

    protected function handleDuplicateSlug(string $field, string $slug)
    {
        $record = DB::table($this->table)->where($field, $slug)->first();
        if (!$record || $this->id) return $slug;

        $maxId = DB::table($this->table)->max('id');
        return $slug.'-'.($maxId + 1);
    }

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        $this->code = isset($this->code) ? $this->code : $this->name;
        $this->code = \Str::slug($this->handleDuplicateSlug('code', $this->code));

        $this->setMetaTags($this->name, $this->desc, $this->meta_keywords);
    }

    /*
     * Accessors
     * ===
     */
    // Main Image Attribute
    public function getMainImageAttribute()
    {
        if ( ! $this->images) {
            return null;
        }
        return optional($this->images)->first();
    }
    // Full Address Attribute
    public function getFullAddressAttribute() {
        $fields = $this->prepareAddressFields();
        if (!Setting::get('full_address_format')) return implode(', ', $fields);
        
        return \Twig::parse(Setting::get('full_address_format'), $fields);
    }
    // Short Address Attribute
    public function getShortAddressAttribute() {
        $fields = $this->prepareAddressFields();
        if (!Setting::get('short_address_format')) return implode(', ', $fields);
        
        return \Twig::parse(Setting::get('short_address_format'), $fields);
    }

    public function prepareAddressFields()
    {
        return array_filter([
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'city' => $this->city,
            'state' => $this->state,
            'zip' => $this->zip,
            'country' => $this->country,
        ]);
    }

    // Map coordinate attribute
    public function getMapCoordinateAttribute() {
        $fields = [
            $this->lat,
            $this->lng,
        ];
        return implode(',', array_filter($fields));
    }

    // Direction link attribute
    public function getDirectionLinkAttribute() {
        $url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&origin=null';
        $destination='&destination='.urlencode($this->map_coordinate);
        $link = $url.$destination;
        return $link;
    }

    // Map marker attribute
    public function getMapMarkerAttribute() {
        return (object)[
            'markerTitle' => $this->name,
            'markerType' => 'coordinate',
            'markerValue' => $this->map_coordinate,
            'markerIcon' => '',
            'markerShadowImage' => '',
            'markerShowInfoWindow' => false,
            'markerInfoWindow' => '<strong>'.$this->name.'</strong><br/>'.$this->full_address.$this->generateDirectionLink(),
        ];
        // return base64_encode(json_encode([$markers]));
    }

    /*
     * Scopes
     * ===
     */
    // Is Default
    public function scopeIsDefault($query)
    {
        return $query->whereNotNull('is_default')->where('is_Default', true);
    }
    public function scopeIsNotDefault($query)
    {
        return $query->whereNull('is_default')->orWhere('is_Default', false);
    }
    public function scopeFilterByType($query, $type=null)
    {
        if (!$type) return $query;
        return $query->where('type', $type);
    }

    /*
     * Helpers
     * ===
     */
    // Generate Direction Link
    public function generateDirectionLink() {
        $url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&origin=null';
        $destination='&destination='.urlencode($this->full_address);
        $link = $url.$destination;
        $html="<hr style='margin-top:5px;margin-bottom:5px;width:100%;'/><div>";
        $html.='<a href="'.$link.'" target="_blank">'.trans('gemfourmedia.gcompany::lang.components.map.props.direction').'</a>';
        $html.="</div>";
        return $html;
    }
    // Type options
    public function getTypeOptions()
    {
        return [
            'headquaters' => trans('gemfourmedia.gcompany::lang.options.address_type.headquaters'),
            'office' => trans('gemfourmedia.gcompany::lang.options.address_type.office'),
            'branch' => trans('gemfourmedia.gcompany::lang.options.address_type.branch'),
            'manufacturer' => trans('gemfourmedia.gcompany::lang.options.address_type.manufacturer'),
            'shop' => trans('gemfourmedia.gcompany::lang.options.address_type.shop'),
        ];
    }
}
