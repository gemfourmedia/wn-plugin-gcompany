<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Client extends Controller
{
    use \GemFourMedia\GCompany\Traits\ControllerHelper;
    
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend.Behaviors.RelationController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'gcompany.client.access', 
        'gcompany.client.create', 
        'gcompany.client.update', 
        'gcompany.client.delete' 
    ];

    public $bodyClass = 'compact-container';
    
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-client');
    }
}
