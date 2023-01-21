<?php

namespace GemFourMedia\GCompany\Classes;

use GemFourMedia\GCompany\Models\Article;
use GemFourMedia\GCompany\Models\Category;

use Winter\Pages\Classes\Page as StaticPage;
use System\Classes\PluginManager;
use Event;

/**
 * Class GCompanyPageExtend
 * @package GemFourMedia\GCompany\Classes
 */
class GCompanyPageExtend
{

    /**
     * @return void
     */
    public function extend()
    {
        $this->registerPageMenuItems();

        if (PluginManager::instance()->exists('Winter.Pages')) {
            $this->extendPageModel();
        }
    }

    protected function registerPageMenuItems()
    {
        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                // Categories
                'gcompany-category' => '[GCompany] Single Category',
                'gcompany-all-categories' => '[GCompany] All Categories',
                'gcompany-group-categories' => '[GCompany] Categories by group',

                // Items
                'gcompany-article' => '[GCompany] Single Article',
                'gcompany-all-articles' => '[GCompany] All Articles',
                'gcompany-category-articles' => '[GCompany] Articles by category',
                'gcompany-group-articles' => '[GCompany] Articles by group',

            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'gcompany-category' || $type == 'gcompany-all-categories' || $type == 'gcompany-group-categories') {
                return Category::getMenuTypeInfo($type);
            }
            elseif ($type == 'gcompany-article' || $type == 'gcompany-all-articles' || $type == 'gcompany-category-articles' || $type == 'gcompany-group-articles') {
                return Article::getMenuTypeInfo($type);
            }

        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'gcompany-category' || $type == 'gcompany-all-categories' || $type == 'gcompany-group-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'gcompany-article' || $type == 'gcompany-all-articles' || $type == 'gcompany-category-articles' || $type == 'gcompany-group-articles') {
                return Article::resolveMenuItem($item, $url, $theme);
            }
        });
    }

        /**
     * @return void
     */
    protected function extendPageModel()
    {
        // StaticPage::extend(function($model) {
        //     $model->addDynamicMethod('listGContentGroups', function() use ($model) {
        //         return $this->getContentGroupFilterOptions();
        //     });

        //     $model->addDynamicMethod('listGContentCategories', function() use ($model) {
        //         return $this->getCategoryFilterOptions();
        //     });
        // });
    }

}