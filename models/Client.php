<?php namespace GemFourMedia\GCompany\Models;

use Model;

/**
 * Model
 */
class Client extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \GemFourMedia\GCompany\Traits\SEOHelper;
    
    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'logo';
    
    /**
     * @var string name of og:type
     */
    public $ogType = 'website';

    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_clients';

    protected $jsonable = ['params'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcompany_clients'],
        'link' => 'max:255',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'desc',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /*
     * Options
     * ===
     */
    public function getTypeOptions()
    {
        return [
            'client' => trans('gemfourmedia.gcompany::lang.options.client_type.client'),
            'partner' => trans('gemfourmedia.gcompany::lang.options.client_type.partner'),
        ];
    }

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        $this->slug = isset($this->slug) ? $this->slug : $this->name;
        $this->slug = \Str::slug($this->slug);

        $this->setMetaTags($this->name, $this->desc, $this->meta_keywords);
    }

    /*
     * Relationships
     * ===
     */
    public $hasMany = [
        'testimonials' => ['GemFourMedia\GCompany\Models\Testimonial', 'client_id'],
    ];

    public $belongsToMany = [
        'portfolios' => [
            'GemFourMedia\GCompany\Models\Article',
            'table' => 'gemfourmedia_gcompany_articles_clients',
            'scope' => 'portfolio',
            'order' => 'sort_order',
        ],
    ];

    /*
     * Accessors
     * ===
     */

    /*
     * Scopes
     * ===
     */
    public function scopeClient($query)
    {
        return $query->whereNotNull('type')->where('type', 'client');
    }
    public function scopePartner($query)
    {
        return $query->whereNotNull('type')->where('type', 'partner');
    }
    public function scopePublished($query)
    {
        return $query->whereNotNull('published')->where('published', true);
    }
    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured')->where('featured', true);
    }
    public function scopeListFrontEnd($query, $options=[])
    {
        extract(array_merge([
            'pageNumber' => 1,
            'perPage' => 12,
            'featuredFilter' => false,
        ], $options));

        $query->published();

        if ($featuredFilter) {
            $query->featured();
        }

        return $query->paginate($perPage, $pageNumber);
    }
}
