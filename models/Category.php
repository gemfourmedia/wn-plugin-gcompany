<?php namespace GemFourMedia\GCompany\Models;

use Model;
use GemFourMedia\GCompany\Models\Article;
use Backend\Classes\BackendController;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Cache;
use Url;

/**
 * Model
 */
class Category extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\NestedTree;
    use \GemFourMedia\GCompany\Traits\SEOHelper;
    
    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'image';
    
    /**
     * @var string name of og:type
     */
    public $ogType = 'website';

    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_categories';

    protected $jsonable = ['params'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcompany_categories'],
        'group' => 'required|max:100',
        'short_desc' => 'max:255',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'short_desc',
        'desc',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        $this->slug = isset($this->slug) ? $this->slug : $this->name;
        $this->slug = \Str::slug($this->slug);

        $this->setMetaTags($this->name, $this->desc, $this->meta_keywords);
    }

    /*
     * Relationships
     * ===
     */
    public $attachOne = [
        'image' => 'System\Models\File',
    ];

    public $hasMany = [
        'articles' => [
            'GemFourMedia\GCompany\Models\Article',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'order' => 'sort_order',
        ],
    ];

    public $belongsToMany = [
        'introductions' => [
            'GemFourMedia\GCompany\Models\Article',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'introduction',
            'order' => 'sort_order',
        ],
        'portfolios' => [
            'GemFourMedia\GCompany\Models\Article',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'portfolio',
            'order' => 'sort_order',
        ],
        'services' => [
            'GemFourMedia\GCompany\Models\Article',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'service',
            'order' => 'sort_order',
        ],
    ];

    /*
     * Accessors
     * ===
     */

    /*
     * Helpers
     * ===
     */
    public function getGroupOptions()
    {
        return [
            'introduction' => trans('gemfourmedia.gcompany::lang.options.group.introduction'),
            'portfolio' => trans('gemfourmedia.gcompany::lang.options.group.portfolio'),
            'service' => trans('gemfourmedia.gcompany::lang.options.group.service'),
        ];
    }

    /*
     * Scopes
     * ===
     */

    public function scopeExcludeArticleCategory($query, $model)
    {
        if (!$model instanceof Article) return $query;
        
        $params = BackendController::$params;
        $group = isset ($model->group) ? $model->group : (isset($params[0]) ? $params[0] : null);
        if (!$group) return $query;

        $categoryId = isset ($model->category_id) ? $model->category_id : \Request::input('Article.category', null);

        return $query->where('group', $group)->where('id', '<>', $categoryId);
    }

    public function scopeIntroduction($query)
    {
        return $query->whereNotNull('group')->where('group', 'introduction');
        
    }
    public function scopePortfolio($query)
    {
        return $query->whereNotNull('group')->where('group', 'portfolio');
    }
    public function scopeService($query)
    {
        return $query->whereNotNull('group')->where('group', 'service');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published')->where('published', true);
    }
    public function scopeFeatured($query)
    {
        return $query->whereNotNull('featured')->where('featured', true);
    }

    /*
     * Helpers
     * ===
     */
    
    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     *
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug,
            'category' => $this->slug,
            'group' => $this->group,
        ];

        return $this->url = $controller->pageUrl($pageName, $params, false);
    }

     /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'gcompany-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($type == 'gcompany-all-categories') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($type == 'gcompany-group-categories') {
            $result = [
                'references'   => [
                    'introduction' => 'Introduction',
                    'portfolio' => 'Portfolio',
                    'service' => 'Service',
                ],
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('companyArticles')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('companyArticles');
                if (!isset($properties['categoryFilter']) || !preg_match('/{{\s*:/', $properties['categoryFilter'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::published()->getNested();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the Url::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \Winter\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'gcompany-category') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $categories = $category->getNested();
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($categories);
            }
        }
        elseif ($item->type == 'gcompany-all-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::published()->orderBy('name')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }
        elseif ($item->type == 'gcompany-group-categories') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $result = [
                'items' => []
            ];

            $categories = self::where('group', $item->reference)->published()->orderBy('name')->get();

            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('companyArticles');
        if (!isset($properties['categoryFilter'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['categoryFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);

        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }
}
