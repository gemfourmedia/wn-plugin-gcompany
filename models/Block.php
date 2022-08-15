<?php namespace GemFourMedia\GCompany\Models;

use Model;

/**
 * Model
 */
class Block extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \Winter\Storm\Database\Traits\Sluggable;

    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_articles_blocks';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|max:255',
        'code' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'subtitle' => 'max:255',
        'published' => 'boolean',
    ];

    /**
     * @var array jsonable fields.
     */
    public $jsonable = ['params'];

    /**
     * @var array Generate slugs for these attributes.
     */
    protected $slugs = ['code' => 'title'];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'subtitle',
        'introtext',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['code', 'index' => true]
    ];


    /**
     * The attributes on which the post list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'           => 'gemfourmedia.gcompany::lang.sorting.title_asc',
        'title desc'          => 'gemfourmedia.gcompany::lang.sorting.title_desc',
        'created_at asc'      => 'gemfourmedia.gcompany::lang.sorting.created_asc',
        'created_at desc'     => 'gemfourmedia.gcompany::lang.sorting.created_desc',
        'updated_at asc'      => 'gemfourmedia.gcompany::lang.sorting.updated_asc',
        'updated_at desc'     => 'gemfourmedia.gcompany::lang.sorting.updated_desc',
        'sort_order asc'      => 'gemfourmedia.gcompany::lang.sorting.manually_asc',
        'sort_order desc'     => 'gemfourmedia.gcompany::lang.sorting.manually_desc',
        'random'              => 'gemfourmedia.gcompany::lang.sorting.random'
    ];

    /*
     * Relationships
     * ===
     */
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $belongsTo = [
        'article' => ['GemFourMedia\GCompany\Models\Article'],
    ];

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        $this->code = isset($this->code) ? $this->code : $this->title;
        $this->code = \Str::slug($this->code);
    }

    /*
     * Accessors
     * ===
     */
    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }

    /*
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true);
    }
}
