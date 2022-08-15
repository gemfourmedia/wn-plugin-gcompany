<?php namespace GemFourMedia\GCompany\Models;

use Model;

/**
 * Model
 */
class Member extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_members';

    protected $jsonable = ['params', 'socials', 'skills'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'avatar' => 'max:255',
        'position' => 'max:255',
        'email' => 'email|max:255',
        'phone' => 'max:255',
        'department' => 'max:255',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'quote',
        'position',
        'department',
    ];

    /*
     * Helpers
     * ===
     */


    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        
    }

    /*
     * Relationships
     * ===
     */


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
