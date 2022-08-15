<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Address extends Controller
{
    use \GemFourMedia\GCompany\Traits\ControllerHelper;
    
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gcompany.address.access', 
        'gcompany.address.create', 
        'gcompany.address.update', 
        'gcompany.address.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-address');
    }
}
