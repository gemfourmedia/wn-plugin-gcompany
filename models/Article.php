<?php namespace GemFourMedia\GCompany\Models;

use Url;
use Html;
use Lang;
use Model;
use Markdown;
use BackendAuth;
use Carbon\Carbon;
use Backend\Models\User;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use Cms\Classes\Controller;
use Winter\Storm\Database\NestedTreeScope;
use Winter\Blog\Classes\TagProcessor;
use ValidationException;

use GemFourMedia\GCompany\Models\Category;
use GemFourMedia\GCompany\Models\Setting;
/**
 * Model
 */
class Article extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Sortable;
    use \Winter\Storm\Database\Traits\Nullable;
    use \GemFourMedia\GCompany\Traits\SEOHelper;
    
    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'main_image';
    
    /**
     * @var string name of og:type
     */
    public $ogType = 'website';
    
    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcompany_articles';

    /**
     * @var array jsonable fields.
     */
    public $jsonable = ['params', 'embeds'];


    /**
     * @var array date fields.
     */
    protected $dates = ['published_at'];

    protected $nullable = ['category_id'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|max:255',
        'code' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcompany_articles'],
        'group' => 'required|max:100',
        'subtitle' => 'max:100',
        'published' => 'boolean',
        'featured' => 'boolean',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'subtitle',
        'introtext',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['code', 'index' => true]
    ];

    /**
     * The attributes on which the post list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'           => 'gemfourmedia.gcompany::lang.sorting.title_asc',
        'title desc'          => 'gemfourmedia.gcompany::lang.sorting.title_desc',
        'created_at asc'      => 'gemfourmedia.gcompany::lang.sorting.created_asc',
        'created_at desc'     => 'gemfourmedia.gcompany::lang.sorting.created_desc',
        'published_at asc'    => 'gemfourmedia.gcompany::lang.sorting.published_asc',
        'published_at desc'   => 'gemfourmedia.gcompany::lang.sorting.published_desc',
        'updated_at asc'      => 'gemfourmedia.gcompany::lang.sorting.updated_asc',
        'updated_at desc'     => 'gemfourmedia.gcompany::lang.sorting.updated_desc',
        'sort_order asc'      => 'gemfourmedia.gcompany::lang.sorting.manually_asc',
        'sort_order desc'     => 'gemfourmedia.gcompany::lang.sorting.manually_desc',
        'random'              => 'gemfourmedia.gcompany::lang.sorting.random'
    ];


    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        $this->code = isset($this->code) ? $this->code : $this->title;
        $this->code = \Str::slug($this->code);

        $this->introtext = str_limit(strip_tags($this->introtext), 191 ,'...');

        if ($this->published && !$this->published_at) {
            $this->published_at = Carbon::now();
        }

        $this->setMetaTags($this->title, $this->introtext, $this->meta_keywords);
    }
    /*
     * Relationships
     * ===
     */
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $belongsTo = [
        'category' => ['GemFourMedia\GCompany\Models\Category'],
    ];

    public $hasMany = [
        'blocks' => ['GemFourMedia\GCompany\Models\Block', 'delete'=>true, 'order'=>'sort_order'],
    ];

    public $belongsToMany = [
        'categories' => [
            'GemFourMedia\GCompany\Models\Category',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'order' => 'nest_left',
        ],
        'introductionCategories' => [
            'GemFourMedia\GCompany\Models\Category',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'introduction',
            'order' => 'nest_left',
        ],
        'portfolioCategories' => [
            'GemFourMedia\GCompany\Models\Category',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'portfolio',
            'order' => 'nest_left',
        ],
        'serviceCategories' => [
            'GemFourMedia\GCompany\Models\Category',
            'table' => 'gemfourmedia_gcompany_articles_categories',
            'scope' => 'service',
            'order' => 'nest_left',
        ],
        'files' => [
            'GemFourMedia\GCompany\Models\File',
            'table' => 'gemfourmedia_gcompany_articles_files',
            'order' => 'sort_order',
        ],
        'clients' => [
            'GemFourMedia\GCompany\Models\Client',
            'table' => 'gemfourmedia_gcompany_articles_clients',
            'order' => 'sort_order',
        ],
    ];
    
    /*
     * Accessors
     * ===
     */
    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }
    /*
     * Scopes
     * ===
     */

    public function scopeWhereGroup($query, $groupName = '') {
        if (!in_array($groupName, array_keys($this->getGroupOptions()))) return $query;

        return $query->$groupName();
    }

    public function scopeWhereCategory($query, int $categoryId) {
        return $query->where('category_id', $categoryId)->orWhereHas('categories', function ($q) use ($categoryId) {
            return $q->where('id', $categoryId);
        });
    }

    public function scopeWhereCategories($query, array $categoryIds) {
        return $query->whereIn('category_id', $categoryIds)->orWhereHas('categories', function ($q) use ($categoryIds) {
            return $q->whereIn('id', $categoryIds);
        });
    }

    public function scopeWithGroupCategories($query, $groupName = '') {
        if (!in_array($groupName, array_keys($this->getGroupOptions()))) return $query;
        
        $groupCategories = $groupName.'Categories';
        return $query->with($groupCategories);
    }

    public function scopeService($query)
    {
        return $query->whereNotNull('group')->where('group', 'service');
    }
    
    public function scopeIntroduction($query)
    {
        return $query->whereNotNull('group')->where('group', 'introduction');
    }
    
    public function scopePortfolio($query)
    {
        return $query->whereNotNull('group')->where('group', 'portfolio');
    }

    public function scopePublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now());
    }

    public function scopeFeatured($query)
    {
        return $query
            ->whereNotNull('featured')
            ->where('featured', true);
    }

    public function scopeListFrontEnd($query, $options = [])
    {
        extract(array_merge([
            'page' => 1,
            'perPage' => 3,
            'group' => null,
            'categoryFilter' => null,
            'featuredFilter' => null,
            'categories' => null,
            'sort' => 'created_at desc',
        ], $options));

        /*
         * Published items only
         */
        $query->published();

        /*
         * Featured items only
         */
        if ($featuredFilter) {
            $query->featured();
        }

        /*
         * Filter by group
         */
        if ($group) {
            $query->whereGroup($group);
        }

        if ($categoryFilter) {
            $query->whereCategory($categoryFilter);
        }

        /*
         * Sorting
         */
        if (in_array($sort, array_keys(static::$allowedSortingOptions))) {
            if ($sort == 'random') {
                $query->inRandomOrder();
            } else {
                @list($sortField, $sortDirection) = explode(' ', $sort);

                if (is_null($sortDirection)) {
                    $sortDirection = "desc";
                }

                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Filter by Categories
         */
        if ($categories !== null) {
            $categories = is_array($categories) ? $categories : [$categories];
            $query->whereCategories($categories);
        }

        /*
         * Return paginated items
         */
        return $query->paginate($perPage, $page);

    }

    public function scopeApplySibling($query, $options = [])
    {
        if (!is_array($options)) {
            $options = ['direction' => $options];
        }

        extract(array_merge([
            'direction' => 'next',
            'attribute' => 'published_at'
        ], $options));

        $isPrevious = in_array($direction, ['previous', -1]);
        $directionOrder = $isPrevious ? 'asc' : 'desc';
        $directionOperator = $isPrevious ? '>' : '<';

        $query->where('group', $this->group);
        $query->where('id', '<>', $this->id);

        if (!is_null($this->$attribute)) {
            $query->where($attribute, $directionOperator, $this->$attribute);
        }

        return $query->orderBy($attribute, $directionOrder);
    }

    /*
     * Helpers
     * ===
     */
    /**
     * Returns the next post, if available.
     * @return self
     */
    public function nextPost()
    {
        return self::published()->applySibling()->first();
    }

    /**
     * Returns the previous post, if available.
     * @return self
     */
    public function previousPost()
    {
        return self::published()->applySibling(-1)->first();
    }
    /**
     * Sets the "url" attribute with a URL to this object.
     * @param string $pageName
     * @param Controller $controller
     * @param array $params Override request URL parameters
     *
     * @return string
     */
    public function setUrl($pageName = null, $controller = null, $params = [])
    {
        $params = array_merge([
            'id'   => $this->id,
            'slug' => $this->code,
        ], $params);

        if (empty($params['category'])) {
            $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
        }

        if (empty($params['group'])) {
            $params['group'] = $this->group;
        }

        $pageName = ($pageName) ? $pageName : Setting::get($this->group.'_detail_page');
        if (!$pageName) return null;
        
        if (!$controller) {
            $controller = new \Cms\Classes\Controller;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    public function getGroupOptions()
    {
        return [
            'introduction' => trans('gemfourmedia.gcompany::lang.options.group.introduction'),
            'portfolio' => trans('gemfourmedia.gcompany::lang.options.group.portfolio'),
            'service' => trans('gemfourmedia.gcompany::lang.options.group.service'),
        ];
    }

    public function getIconOptions()
    {
        return \GemFourMedia\GWinterHelper\Classes\IconList::getList();
    }


    public function getFrontEndUrl()
    {
        $theme = Theme::getActiveTheme();

        $pages = CmsPage::listInTheme($theme, true);

        foreach ($pages as $page) {
            if (!$page->hasComponent('companyArticle')) {
                continue;
            }
            $properties = $page->getComponentProperties('companyArticle');
            if (!isset($properties['slug'])) {
                continue;
            }

            if ($properties['slug'] == $this->code) {
                return $this->setUrl($page->baseFileName);
            }
            else {
                return $this->setUrl();
            }
        }
        return null;
    }



    // Sort Ordering block
    public function setBlockOrder($itemIds, $itemOrders = null)
     {
        if ( ! is_array($itemIds)) {
            $itemIds = [$itemIds];
        }

        if ($itemOrders === null) {
            $itemOrders = $itemIds;
        }

        if (count($itemIds) != count($itemOrders)) {
            throw new Exception('Invalid setRelationOrder call - count of itemIds do not match count of itemOrders');
        }

        foreach ($itemIds as $index => $id) {
            $order    = $itemOrders[$index];

            DB::table('gemfourmedia_gcompany_articles_blocks')
              ->where('article_id', $this->id)
              ->where('id', $id)
              ->update(['sort_order' => $order]);
        }
    }

    //
    // Menu helpers
    //

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
     *
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];
        // Single item
        if ($type == 'gcompany-article') {
            $references = [];

            $items = self::get();
            foreach ($items as $item) {
                $references[$item->id] = $item->title;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }
        // All Items
        if ($type == 'gcompany-all-articles') {
            $result = [
                'dynamicItems' => true
            ];
        }

        // Items filter by category
        if ($type == 'gcompany-category-articles') {
            $references = [];

            $references = Category::listSubCategoryOptions();

            $result = [
                'references'   => $references,
                'dynamicItems' => true
            ];
        }

        // Items filter by content group
        if ($type == 'gcompany-group-articles') {
            $references = [
                'introduction' => 'Introduction',
                'portfolio' => 'Portfolio',
                'service' => 'Service',
            ];

            $result = [
                'references'   => $references,
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];

            foreach ($pages as $page) {
                if (!$page->hasComponent('companyArticle')) {
                    continue;
                }

                /*
                 * Component must use a categoryPage filter with a routing parameter and post slug
                 * eg: categoryPage = "{{ :somevalue }}", slug = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('companyArticle');
                if (!isset($properties['categoryPage']) || !preg_match('/{{\s*:/', $properties['slug'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
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
     *
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;
        // Single Item
        if ($item->type == 'gcompany-article') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $GCItem = self::find($item->reference);
            if (!$GCItem) {
                return;
            }

            $pageUrl = self::getArticlePageUrl($item->cmsPage, $GCItem, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $GCItem->updated_at;
        }
        // All Items
        elseif ($item->type == 'gcompany-all-articles') {
            $result = [
                'items' => []
            ];

            $GCItems = self::published()
            ->orderBy('title')
            ->get();

            foreach ($GCItems as $GCItem) {
                $itemDetail = [
                    'title' => $GCItem->title,
                    'url'   => self::getArticlePageUrl($item->cmsPage, $GCItem, $theme),
                    'mtime' => $GCItem->updated_at
                ];

                $itemDetail['isActive'] = $itemDetail['url'] == $url;

                $result['items'][] = $itemDetail;
            }
        }
        // Items filter by Category| Content Group | Serie
        elseif ($item->type == 'gcompany-category-articles') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = Category::find($item->reference);
            if (!$category) {
                return;
            }

            $result = [
                'items' => []
            ];

            $query = self::published()
            ->orderBy('title');

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->withoutGlobalScope(NestedTreeScope::class)->whereIn('id', $categories);
            });

            $articles = $query->get();

            foreach ($articles as $article) {
                $articleItem = [
                    'title' => $article->title,
                    'url'   => self::getArticlePageUrl($item->cmsPage, $article, $theme),
                    'mtime' => $article->updated_at
                ];

                $articleItem['isActive'] = $articleItem['url'] == $url;

                $result['items'][] = $articleItem;
            }
        }
        elseif ($item->type == 'gcompany-group-articles') {
            $query = self::published()->orderBy('title');
            $articles = $query->where('group', $item->reference)->get();

            foreach ($articles as $article) {
                $articleItem = [
                    'title' => $article->title,
                    'url'   => self::getArticlePageUrl($item->cmsPage, $article, $theme),
                    'mtime' => $article->updated_at
                ];

                $articleItem['isActive'] = $articleItem['url'] == $url;

                $result['items'][] = $articleItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a item page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getArticlePageUrl($pageCode, $item, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('companyArticle');
        if (!isset($properties['slug'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the item filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $params = [
            $paramName => $item->code,
            'year'  => $item->published_at->format('Y'),
            'month' => $item->published_at->format('m'),
            'day'   => $item->published_at->format('d')
        ];
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }

}
