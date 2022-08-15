<?php

namespace GemFourMedia\GCompany\Traits;

use Illuminate\Support\Facades\Event;
use Backend\Behaviors\FormController;
use Db;
use Flash;
use Lang;

/**
 * Class ControllerHelper
 * @package GemFourMedia\GCompany\Traits
 */
trait GroupControllerHelper
{
	use \Backend\Traits\FormModelSaver;

    // Bulk Delete
    public function index_onDelete()
    {
        if(!$this->checkPermissions('delete')) {
            return \Response::make('Access Denined', 403);
        }
        $listConfig = $this->listGetConfig();
        if ($listConfig && isset($listConfig->modelClass))
            $model = new $listConfig->modelClass;
        if ($model) {
            if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
                foreach ($checkedIds as $recordID) {
                    if ((!$record = $model->find($recordID))) {
                        continue;
                    }

                    $record->delete();
                }
                Flash::success(Lang::get('gemfourmedia.gcompany::lang.messages.delete_success'));
            }

            $type = isset($this->vars['currentType']) ? $this->vars['currentType'] : '';
            return $this->listRefresh($type);
        }
    }

    // Save a copy of current record
    public function onSaveCopy()
    {

        $model = $this->formCreateModelObject();
        $model = $this->formExtendModel($model) ?: $model;

        $this->initForm($model);

        $this->formBeforeSave($model);
        $this->formBeforeCreate($model);

        $widget = $this->formGetWidget();
        $modelsToSave = $this->prepareModelsToSave($model, $widget->getSaveData());

        Db::transaction(function () use ($modelsToSave, $widget) {
            foreach ($modelsToSave as  $k => $modelToSave) {
                $modelToSave->save(null, $widget->getSessionKey());
            }
        });

        $this->formAfterSave($model);
        $this->formAfterCreate($model);

        Flash::success(Lang::get('gemfourmedia.gcompany::lang.messages.save_as_copy_success'));

        if ($redirect = $this->makeRedirect('create', $model)) {
            return $redirect;
        }
    }

    protected function checkPermissions($action) {
        $type = isset($this->vars['currentType']) ? $this->vars['currentType'] : null;
        $type = ($type) ? $type: strtolower(class_basename(get_class($this)));

    	$permission = 'gcompany.'.$type.'.'.$action;
    	$requiredPermission = in_array($permission, $this->requiredPermissions);
    	if ($requiredPermission) {
    		($this->user->hasAccess($permission));
    		return $this->user->hasAccess($permission);
    	}
    	return true;
    }

    public function create($type, $context='')
    {
        if(!$this->checkPermissions('create')) {
            return \Response::make('Access Denined', 403);
        }
        return $this->asExtension('FormController')->create($context);
    }

    public function create_onSave($type, $context='')
    {
    	if(!$this->checkPermissions('create')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->create_onSave($context);
    }

    public function update($type, $recordId, $context = null)
    {
        if(!$this->checkPermissions('update')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->update($recordId);
    }

    public function update_onSave($type, $recordId, $context = null)
    {
        if(!$this->checkPermissions('update')) {
            return \Response::make('Access Denined', 403);
        }
        return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }

    public function update_onDelete($type, $recordId = null)
    {
        if(!$this->checkPermissions('delete')) {
    		return \Response::make('Access Denined', 403);
    	}
        return $this->asExtension('FormController')->update_onDelete($recordId);
    }

    public function reorderExtendQuery($query)
    {
        if (!isset($this->params[0])) return;
        $type = $this->params[0];

        if (in_array($this->params[0], $this->allowedList)) {
            $query->{$type}();
        }

    }

    public function listExtendQuery($query, $definition)
    {
        
        if (in_array($definition, $this->allowedList)) {
            $query->{$definition}();
        }
    }
}