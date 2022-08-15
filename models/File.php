<?php namespace GemFourMedia\GCompany\Models;

use Model;

/**
 * Model
 */
class File extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_files';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|max:255',
        'image' => 'max:255',
        'published' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'desc',
    ];

    /*
     * Relationships
     * ===
     */
    public $attachMany = [
        'attachments' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    /*
     * Accessors
     * ===
     */
    public function getAttachmentsCountAttribute()
    {
        if ($this->attachments) return $this->attachments->count();
    }

    /*
     * Scopes
     * ===
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published')->where('published', true);
    }

    public function scopeListFrontEnd($query, $options=[])
    {
        extract(array_merge([
            'pageNumber' => 1,
            'perPage' => 12
        ], $options));

        $query->published();

        return $query->paginate($perPage, $pageNumber);
    }
}