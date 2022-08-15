<?php namespace GemFourMedia\GCompany\Controllers;

use Lang;
use Flash;
use Config;
use Request;
use Backend;
use BackendMenu;
use System\Classes\SettingsManager;
use Backend\Classes\Controller;
use ApplicationException;
use Exception;
use GemFourMedia\GCompany\Models\Info as InfoModel;

class Info extends Controller
{
    /**
     * @var WidgetBase Reference to the widget object.
     */
    protected $formWidget;


    public $requiredPermissions = [
        'gcompany.info.access', 
        'gcompany.info.update' 
    ];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('GemFourMedia.GCompany', 'gcompany-main-menu', 'gcompany-menu-info');
        SettingsManager::setContext('GemFourMedia.GCompany', 'gcompany-info');
    }

    public function index()
    {
        $this->pageTitle = 'gemfourmedia.gcompany::lang.menus.info';
        SettingsManager::setContext('gemfourmedia.gcompany', 'gcompany-info');

        $model = $this->createModel();
        $this->initWidgets($model);
    }

    //
    // Generated Form
    //

    public function index_onSave()
    {

        $model = $this->createModel();
        $this->initWidgets($model);

        $saveData = $this->formWidget->getSaveData();
        foreach ($saveData as $attribute => $value) {
            $model->{$attribute} = $value;
        }
        $model->save(null, $this->formWidget->getSessionKey());

        Flash::success(Lang::get('system::lang.settings.update_success', ['name' => Lang::get('Company Info')]));
    }

    public function index_onResetDefault()
    {

        $model = $this->createModel();
        $model->resetDefault();

        Flash::success(Lang::get('backend::lang.form.reset_success'));
        return Backend::redirect('gemfourmedia/gcompany/info');
    }

    /**
     * Render the form.
     */
    public function formRender($options = [])
    {
        if (!$this->formWidget) {
            throw new ApplicationException(Lang::get('backend::lang.form.behavior_not_ready'));
        }

        return $this->formWidget->render($options);
    }

    /**
     * Returns the form widget used by this behavior.
     *
     * @return \Backend\Widgets\Form
     */
    public function formGetWidget()
    {
        if (is_null($this->formWidget)) {
            $model = $this->createModel();
            $this->initWidgets($model);
        }

        return $this->formWidget;
    }

    /**
     * Prepare the widgets used by this action
     * Model $model
     */
    protected function initWidgets($model)
    {
        $config = $model->getFieldConfig();
        $config->model = $model;
        $config->arrayName = class_basename($model);
        $config->context = 'update';

        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        $this->formWidget = $widget;
    }

    /**
     * Internal method, prepare the list model object
     */
    protected function createModel()
    {
        return InfoModel::instance();
    }

}
