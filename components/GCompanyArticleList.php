<?php namespace GemFourMedia\GCompany\Components;

use Cms\Classes\ComponentBase;
use GemFourMedia\GCompany\Models\Article;
use GemFourMedia\GCompany\Models\Category;

use Lang;
use Redirect;

class GCompanyArticleList extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;
    use \GemFourMedia\GCompany\Traits\PageMetaHelper;

    public $title;
    public $subtitle;
    public $pageNumber;
    
    public $detailPage;
    public $categoryPage;
    
    public $type;
    public $items;
    public $category;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.articleList.name',
            'description' => 'gemfourmedia.gcompany::lang.components.articleList.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'perPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.perPage',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcompany::lang.components.articleList.props.numeric_validation',
                'default'     => 6,
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'pageNumber' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.pageNumber',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcompany::lang.components.articleList.props.numeric_validation',
                'default'     => 1,
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'sortOrder' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.sortOrder',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.sortOrder_comment',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'groupFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.groupFilter',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.groupFilter_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupArticleFilter',
                'type' => 'dropdown',
                'default' => '{{:group}}',
            ],
            'categoryFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.categoryFilter',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.categoryFilter_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupArticleFilter',
                'type' => 'checkbox',
                'default' => '{{:category}}',
            ],
            'featuredFilter' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.featuredFilter',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.featuredFilter_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupArticleFilter',
                'type' => 'checkbox',
                'default' => null,
                'showExternalParam' => false,
            ],
            'detailPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.detailPage',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.detailPage_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupLink',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'categoryPage' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.categoryPage',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.categoryPage_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupLink',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'setPageMeta' => [
                'title' => 'gemfourmedia.gcompany::lang.components.articleList.props.setPageMeta',
                'comment' => 'gemfourmedia.gcompany::lang.components.articleList.props.setPageMeta_comment',
                'group'=> 'gemfourmedia.gcompany::lang.components.articleList.props.groupAdvance',
                'type' => 'checkbox',
                'default' => true,
                'showExternalParam' => false,
            ],
        ];
    }

    public function getFilterCategoryOptions()
    {
        return ['' => 'Select Category'] + Category::lists('name', 'slug');
    }

    public function getSortOrderOptions()
    {
        $options = Article::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public function getGroupFilterOptions()
    {
        return $this->getAllowedArticleShowCase();
    }

    public function getDetailPageOptions()
    {
        return ['' => 'Select page'] + $this->listGCompanyCmsPages('companyArticle');
    }

    public function getCategoryPageOptions()
    {
        return ['' => 'Select page'] + $this->listGCompanyCmsPages('companyArticles');
    }

    public function onRun()
    {
        $this->prepareVars();
        
        $this->items = $this->page['items'] = $this->loadArticles();

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
        if (!$this->items) {
            $this->prepareVars();
        
            $this->items = $this->page['items'] = $this->loadArticles();
        }
    }

    public function prepareVars()
    {
        // Widget vars
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');

        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');

        $this->detailPage = $this->page['detailPage'] = $this->property('detailPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');

        $this->type = $this->page['type'] = $this->property('groupFilter');

        $this->category = $this->page['category'] = $this->loadCategory();
        
    }

    public function loadArticles()
    {

        $categoryId = ($this->category) ? $this->category->id : null;
        $categorySlug = ($this->category) ? $this->category->slug : null;
        
        $options = [
            'perPage' => $this->property('perPage', 3),
            'page' => $this->property('pageNumber', 1),
            'sort' => $this->property('sortOrder', 'created_at asc'),
            'group' => $this->type,
            'categoryFilter' => $categoryId,
            'featuredFilter' => $this->property('featuredFilter'),
        ];
        
        $items = Article::with(['images', 'category', 'categories'])->listFrontEnd($options);
        $items->each(function ($item) use ($categorySlug) {

            $item->setUrl($this->detailPage, $this->controller, ['category' => $categorySlug]);

            if ($item->category) {
                $item->category->setUrl($this->categoryPage, $this->controller);
            }
            if ($item->categories) {
                $item->categories->each(function($category) {
                    $category->setUrl($this->categoryPage, $this->controller);
                });
            }
            
        });

        return $items;
    }

    public function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) return;
        
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
}
