<?php namespace GemFourMedia\GCompany\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Backend\Classes\BackendController;

use GemFourMedia\GCompany\Models\Article as ArticleModel;

class Article extends Controller
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

    public $bodyClass = 'compact-container';

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

    public function __construct()
    {
        $this->vars['currentType'] = '';

        $action = BackendController::$action;
        $params = BackendController::$params;
        $currentType = isset ($params[0]) ? $params[0] : null;

        if (in_array($currentType, $this->allowedList)) {
            $this->vars['currentType'] = $currentType;
            $this->formConfig = 'config_form_'.$currentType.'.yaml';
            BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-'.$currentType);
        }

        parent::__construct();
        $this->addJs('/plugins/gemfourmedia/gcompany/assets/js/Sortable.js');
        $this->addJs('/plugins/gemfourmedia/gcompany/assets/js/backend.js');
    }

    public function onReorderRelation()
    {
        $records = request()->input('rcd');

        $model   = ArticleModel::findOrFail($this->params[1]);
        $model->setBlockOrder($records, range(1, count($records)));

        Flash::success('Block sorting updated');
    }
}
