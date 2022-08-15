<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Feedback extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gcompany.feedback.access', 
        'gcompany.feedback.update', 
        'gcompany.feedback.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-feedback');
    }
}
