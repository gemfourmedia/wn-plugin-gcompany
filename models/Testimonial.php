<?php namespace GemFourMedia\GCompany\Models;

use Model;
use Carbon\Carbon;

/**
 * Model
 */
class Testimonial extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_testimonials';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'max:255',
        'rating' => 'numeric',
        'company' => 'max:255',
        'webpage' => 'max:255',
        'reviewer_avatar' => 'max:255',
        'reviewer_name' => 'max:255',
        'reviewer_position' => 'max:255',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'company',
        'content',
        'reviewer_name',
        'reviewer_position',
    ];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at'];

     /*
     * Options
     * ===
     */
    public function getRatingOptions()
    {
        return [
            1 => trans('gemfourmedia.gcompany::lang.options.rating.1_star'),
            2 => trans('gemfourmedia.gcompany::lang.options.rating.2_stars'),
            3 => trans('gemfourmedia.gcompany::lang.options.rating.3_stars'),
            4 => trans('gemfourmedia.gcompany::lang.options.rating.4_stars'),
            5 => trans('gemfourmedia.gcompany::lang.options.rating.5_stars'),
        ];
    }

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        if ($this->published && !$this->published_at) {
            $this->published_at = Carbon::now();
        }

    }

    /*
     * Relationships
     * ===
     */
    public $belongsTo = [
        'client' => ['GemFourMedia\GCompany\Models\Client', 'client_id'],
    ];

    public $attachMany = [
        'provements' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    /*
     * Accessors
     * ===
     */

    /*
     * Scopes
     * ===
     */
    public function scopePublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
        ;

    }
    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured')->where('featured', true);
    }

    public function scopeListFrontEnd($query, $options=[])
    {
        extract(array_merge([
            'pageNumber' => null,
            'perPage' => 12,
            'featuredFilter' => false,
        ], $options));

        $query->with(['client', 'provements'])->published();

        if ($featuredFilter) {
            $query->featured();
        }

        return $query->paginate($perPage, $pageNumber);
    }
}
