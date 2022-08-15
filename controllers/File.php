<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class File extends Controller
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
        'gcompany.file.access', 
        'gcompany.file.create', 
        'gcompany.file.update', 
        'gcompany.file.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-file');
    }
}
