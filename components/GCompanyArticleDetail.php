<?php namespace GemFourMedia\GCompany\Components;

use Event;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use GemFourMedia\GCompany\Models\Article;

class GCompanyArticleDetail extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\ListOptionsHelper;
    use \GemFourMedia\GCompany\Traits\PageMetaHelper;

    public $title;
    public $subtitle;
    public $pageNumber;

    public $detailPage;
    public $categoryPage;

    public $item;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.article.name',
            'description' => 'gemfourmedia.gcompany::lang.components.article.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'gemfourmedia.gcompany::lang.components.article.props.slug',
                'description' => 'gemfourmedia.gcompany::lang.components.article.props.slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'dropdown'
            ],
            'categoryPage' => [
                'title'       => 'gemfourmedia.gcompany::lang.components.article.props.categoryPage',
                'description' => 'gemfourmedia.gcompany::lang.components.article.props.categoryPage_desc',
                'type'        => 'dropdown',
                'default'     => '',
                'showExternalParam' => false,
            ],
            'setPageMeta' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.article.props.setPageMeta',
                'description'       => 'gemfourmedia.gcompany::lang.components.article.props.setPageMeta_desc',
                'type'              => 'checkbox',
                'default'           => true,
                'group'             => 'gemfourmedia.gcompany::lang.components.article.props.group_advanced',
                'showExternalParam' => false,
            ],
        ];
    }
    public function getSlugOptions()
    {
            return [''=>'Select'] + Article::published()->get()->lists('title', 'code');
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            if (isset($params['slug'])) {
                $records = Article::transWhere('code', $params['slug'], $oldLocale)->first();
                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams['slug'] = $records['code'];
                }
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->item = $this->page['item'] = $this->loadItem();

        if (!$this->item) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
    }

    public function onRender()
    {
        if (!$this->item) {
            $this->item = $this->page['item'] = $this->loadItem();
        }
    }

    public function loadItem()
    {
        if (!$slug = $this->property('slug')) return;
        $item = new Article;
        $query = $item->query();

        if ($item->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('code', $slug);
        } else {
            $query->where('code', $slug);
        }

        $item = $query->with(['images', 'category', 'blocks' ,'files', 'categories', 'clients'])->published()->first();

        /*
         * Add a "url" helper attribute for linking to each category
         */
        if ($item && $item->exists) {
            if ($item->categories){
                $item->categories->each(function($category) {
                    $category->setUrl($this->listPage, $this->controller);
                });
            }
            if ($this->property('setPageMeta')) {
                $this->setPageMeta($item);
            }
        }

        return $item;
    }

}
