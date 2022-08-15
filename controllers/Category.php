<?php namespace GemFourMedia\GCompany\Controllers;

use Backend\Classes\Controller;
use Backend\Classes\BackendController;
use BackendMenu;

class Category extends Controller
{
    use \GemFourMedia\GCompany\Traits\GroupControllerHelper;

    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend\Behaviors\ReorderController',
        'Backend\Behaviors\RelationController',
    ];
    
    public $listConfig = [
        'introduction' => 'config_list_introduction.yaml',
        'portfolio' => 'config_list_portfolio.yaml',
        'service' => 'config_list_service.yaml',
    ];

    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'gcompany.introduction.access', 
        'gcompany.introduction.create', 
        'gcompany.introduction.update', 
        'gcompany.introduction.delete',

        'gcompany.portfolio.access', 
        'gcompany.portfolio.create', 
        'gcompany.portfolio.update', 
        'gcompany.portfolio.delete', 

        'gcompany.service.access', 
        'gcompany.service.create', 
        'gcompany.service.update', 
        'gcompany.service.delete', 
    ];

    public $allowedList = [
        'introduction',
        'portfolio',
        'service'
    ];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        $this->vars['currentType'] = '';

        $action = BackendController::$action;
        $params = BackendController::$params;
        $currentType = isset ($params[0]) ? $params[0] : null;

        if (in_array($currentType, $this->allowedList)) {
            $this->initActionType($currentType);
        }
        parent::__construct();

    }

    public function initActionType($type = '')
    {
        if (!$type) return;
        
        $this->vars['currentType'] = $type;

        $this->formConfig = 'config_form_'.$type.'.yaml';
        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-'.$type);
    }

    /**
     * Override Controller Actions
     * ===
     */

/*    public function index()
    {
        return $this->asExtension('ListController')->index();
    }

    public function create($type, $context='')
    {
        return $this->asExtension('FormController')->create($context);
    }

    public function update($type, $recordId, $context = null)
    {
        return $this->asExtension('FormController')->update($recordId, $context);
    }

    // Introduction Update OnSave
    public function update_onSave($type, $recordId, $context = null)
    {
        return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }
    // Introduction Update OnDelete
    public function update_onDelete($type, $recordId)
    {
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }

    // List Extends
    public function listExtendQuery($query, $definition)
    {
        
        if (in_array($definition, $this->allowedList)) {
            $query->{$definition}();
        }
    }

    public function reorderExtendQuery($query)
    {
        if (!isset($this->params[0])) return;
        $type = $this->params[0];

        if (in_array($this->params[0], $this->allowedList)) {
            $query->{$type}();
        }

    }*/
}
