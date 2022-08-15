<?php namespace GemFourMedia\GCompany\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use GemFourMedia\GCompany\Models\Category;

class GCompanyCategories extends ComponentBase
{
    // use \GemFourMedia\GCompany\Traits\ListOptionsHelper;

    public $categories;
    public $currentCategory;

    public $listPage;
    public $detailPage;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.categories.name',
            'description' => 'gemfourmedia.gcompany::lang.components.categories.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'groupFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.categories.props.groupFilter',
                'description' => 'gemfourmedia.gcompany::lang.components.categories.props.groupFilter_comment',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'categoryFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.categories.props.categoryFilter',
                'description' => 'gemfourmedia.gcompany::lang.components.categories.props.categoryFilter_comment',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'currentCategory' => [
                'title' => 'gemfourmedia.gcompany::lang.components.categories.props.currentCategory',
                'description' => 'gemfourmedia.gcompany::lang.components.categories.props.currentCategory_comment',
                'type' => 'dropdown',
                'default' => '{{:category}}'
            ],
            'featured' => [
                'title' => 'gemfourmedia.gcompany::lang.components.categories.props.featured',
                'description' => 'gemfourmedia.gcompany::lang.components.categories.props.featured_comment',
                'type' => 'checkbox',
                'showExternalParam' => false,
            ],
            'withArticles' => [
                'title' => 'Load articles',
                'description' => 'Load articles belongs to category',
                'type' => 'checkbox',
                'showExternalParam' => false,
            ],
            'detailPage' => [
                'title' => 'Detail Page',
                'description' => 'Detail Page',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'listPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.categories.props.listPage',
                'description' => 'gemfourmedia.gcompany::lang.components.categories.props.listPage_comment',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
        ];
    }

    public function getGroupFilterOptions()
    {
        return [
            'introduction' => trans('gemfourmedia.gcompany::lang.options.group.introduction'),
            'portfolio' => trans('gemfourmedia.gcompany::lang.options.group.portfolio'),
            'service' => trans('gemfourmedia.gcompany::lang.options.group.service'),
        ];
    }

    public function getCategoryFilterOptions()
    {
        return ['' => 'Select Category'] + Category::lists('name', 'slug');
    }

    public function getCurrentCategoryOptions()
    {
        return ['' => 'Select Category'] + Category::lists('name', 'slug');
    }

    public function getListPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getDetailPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->categories = $this->loadCategories();
        $this->currentCategory = $this->loadCurrentCategory();
    }

    public function onRender()
    {
        if (!$this->categories) {
            $this->prepareVars();
            $this->categories = $this->loadCategories();
            $this->currentCategory = $this->loadCurrentCategory();
        }
    }

    public function prepareVars()
    {
        $this->listPage = $this->page['listPage'] = $this->property('listPage');
        $this->detailPage = $this->page['detailPage'] = $this->property('detailPage');
    }

    public function loadCategories()
    {
        $categories = new Category;

        $query = $categories->query();

        if ($this->property('featured')) $query->featured();

        if ($this->property('groupFilter')) $query->{$this->property('groupFilter')}();

        if ($this->property('withArticles')) $query->with('articles');

        $categories = $query->with('image')->get();

        if ($categories) {
            $categories->each(function ($category) {
                $category->url = $category->setUrl($this->listPage, $this->controller);
            });
        }
        return $categories;
    }

    public function loadCurrentCategory()
    {
        if (!$slug = $this->property('currentCategory')) return;

        $category = new Category;
        $query = $category->query();

        if ($category->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }

        $category = $query->with('image')->first();

        if ($category && $this->property('setPageMeta')) {
            $this->setPageMeta($category);
        }

        return $category;
    }

    public function getCategoryPageUrl($categorySlug = '')
    {

        $theme = Theme::getActiveTheme();

        $pages = Page::listInTheme($theme, true);

        foreach ($pages as $page) {
            if (!$page->hasComponent('companyArticles')) {
                continue;
            }
            $properties = $page->getComponentProperties('companyArticles');
            if (!isset($properties['categoryFilter'])) {
                continue;
            }
            if ($properties['categoryFilter'] != $categorySlug) {
                continue;
            }
            return $page->baseFileName;
        }

        return null;
    }
}
