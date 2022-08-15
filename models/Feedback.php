<?php namespace GemFourMedia\GCompany\Models;

use Model;
use Lang;

/**
 * Model
 */
class Feedback extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_feedbacks';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'form' => 'required|max:100',
        'status' => 'required|max:100',
    ];
 
    /**
     * @var array fillable fields
     */
    protected $fillable = [
        'form', 'data', 'status'
    ];

    /**
     * @var array fillable fields
     */
    protected $jsonable = ['data'];


    public function getFormTypeOptions(){
        return [
            'contact' => 'gemfourmedia.gcompany::lang.options.contact',
            'testimonial' => 'gemfourmedia.gcompany::lang.options.testimonial',
        ];
    }

    public function getStatusOptions()
    {
        return [
            'pending' =>  trans('gemfourmedia.gcompany::lang.options.pending'),
            'contacted' =>  trans('gemfourmedia.gcompany::lang.options.contacted'),
            'finalized' =>  trans('gemfourmedia.gcompany::lang.options.finalized'),
        ];
    }
}
