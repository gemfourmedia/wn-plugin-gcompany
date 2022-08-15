<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Member extends Controller
{
    use \GemFourMedia\GCompany\Traits\ControllerHelper;
    
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController'
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'gcompany.member.access', 
        'gcompany.member.create', 
        'gcompany.member.update', 
        'gcompany.member.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-member');
    }
}
