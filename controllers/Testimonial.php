<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Testimonial extends Controller
{
    use \GemFourMedia\GCompany\Traits\ControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gcompany.testimonial.access', 
        'gcompany.testimonial.create', 
        'gcompany.testimonial.update', 
        'gcompany.testimonial.delete' 
    ];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-testimonial');
    }
}
