<?php namespace GemFourMedia\GCompany\Components;

use Cms\Classes\ComponentBase;
use Redirect;


class GCompanyShowCase extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;

    public $cssClass;
    public $title;
    public $subtitle;
    public $enableAssets;

    public $pageParam;
    
    public $type;
    public $items;

    protected $model;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.showCase.name',
            'description' => 'gemfourmedia.gcompany::lang.components.showCase.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'showCaseType' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.showCaseType',
                'type' => 'dropdown',
                'default' => 'testimonial',
            ],
            'perPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.perPage',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcompany::lang.components.showCase.props.numeric_validation',
                'default'     => 6,
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'pageNumber' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.pageNumber',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcompany::lang.components.showCase.props.numeric_validation',
                'default'     => 1,
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'featuredFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.featuredFilter',
                'comment' => 'gemfourmedia.gcompany::lang.components.showCase.props.featuredFilter_comment',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'clientPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.showCase.props.clientPage',
                'comment' => 'gemfourmedia.gcompany::lang.components.showCase.props.clientPage_comment',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
        ];
    }


    public function getDetailPageOptions()
    {
        return ['' => 'Select Page'] + $this->listGCompanyCmsPages('companyClient');
    }

    public function getShowCaseTypeOptions()
    {
        return $this->getAllowedShowCase();
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->items = $this->page['items'] = $this->loadItems();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->items->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    public function onRender()
    {
        if (!$this->model || !$this->items) {
            $this->prepareVars();
            $this->items = $this->page['items'] = $this->loadItems();
        }
    }

    public function prepareVars()
    {
        // Widget vars
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');

        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');

        $this->clientPage = $this->page['clientPage'] = $this->property('clientPage');
        $this->type = $this->page['type'] = $this->property('showCaseType');

        // Avoid in case dynamic type, it should be in allowed list: testimonial | client | file | member
        if (!in_array($this->property('showCaseType'), array_keys($this->getShowCaseTypeOptions()))) return;
        $type = ($this->type == 'partner') ? 'client' : $this->type;

        $modelClass = '\\GemFourMedia\\GCompany\\Models\\'.ucfirst($type);
        $this->model = (new $modelClass);
    }

    public function loadItems($pageNumber = null)
    {
        if (!$this->model) return;
       
        $pageNumber = ($pageNumber) ? $pageNumber : $this->property('pageNumber');
        
        $options = [
            'perPage' => $this->property('perPage', 1),
            'pageNumber' => $pageNumber,
            'featuredFilter' => $this->property('featuredFilter', 'false'),
        ];
        
        $items = $this->model->listFrontEnd($options);
        
        if ($this->type == 'partner') {
            $items = $this->model->partner()->listFrontEnd($options);
        }
        
        if ($this->type == 'client') {
            $items = $this->model->client()->listFrontEnd($options);
        }
        
        if (!$items) return;
        
        if ($this->type == 'client' && $this->property('clientPage')) {        
            $items->each(function ($item) {
                $item->url = $this->controller->pageUrl($this->property('clientPage'), ['slug' => $item->slug]);
            });
        }

        return $items;
    }

    public function onPaginate()
    {
        $this->prepareVars();
        $pageNumber = post('pageNumber', 1);

        if (!$this->items) {
            $this->items = $this->loadItems($pageNumber);
        }

        if ($pageNumber > ($lastPage = $this->items->lastPage()) && $pageNumber > 1 ) {
            $pageNumber = $this->items->lastPage();
            $this->items = $this->loadItems($pageNumber);
        }
        $this->page['items'] = $this->items;
    }

    public function onLoadDetail()
    {
        $this->prepareVars();
        if (!$this->model) return;
        $this->page['item'] = $this->model->find(post('itemID'));
    }
}
