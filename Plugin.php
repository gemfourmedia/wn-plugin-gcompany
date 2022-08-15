<?php namespace GemFourMedia\GCompany;

use System\Classes\PluginBase;
use Backend;
use Event;
use GemFourMedia\GCompany\Models\Info;
use GemFourMedia\GCompany\Classes\GCompanyPageExtend;
use GemFourMedia\GCompany\Classes\GCompanySearchResultsProvider;

class Plugin extends PluginBase
{

    public function boot()
    {
    	Event::listen('cms.page.beforeDisplay', function($controller, $url, $page) {
            $controller->vars['gcompany'] = Info::instance();
        });

        Event::listen('offline.sitesearch.extend', function () {
            return new GCompanySearchResultsProvider();
        });
    }

    public function register()
    {
        (new GCompanyPageExtend)->extend();
    }

    public function registerSettings()
    {
        return [
            'gcompany-setting' => [
                'label'       => 'gemfourmedia.gcompany::lang.settings.setting.label',
                'description' => 'gemfourmedia.gcompany::lang.settings.setting.desc',
                'category'    => 'GCompany',
                'icon'        => 'icon-cogs',
                'class'         => 'GemFourMedia\GCompany\Models\Setting',
                'order'       => 300,
                'permissions' => ['gcompany.access_setting'],
                'keywords'    => 'gcompany setting'
            ],
            'gcompany-info' => [
                'label'       => 'gemfourmedia.gcompany::lang.settings.info.label',
                'description' => 'gemfourmedia.gcompany::lang.settings.info.desc',
                'category'    => 'GCompany',
                'icon'        => 'icon-info',
                'url'         => Backend::url('gemfourmedia/gcompany/info'),
                'order'       => 301,
                'keywords'    => 'gcompany company info'
            ],
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'GemFourMedia\GCompany\Components\GCompanyCategories' => 'companyCategories',

            'GemFourMedia\GCompany\Components\GCompanyInfo' => 'companyInfo',
            'GemFourMedia\GCompany\Components\GCompanyAddress' => 'companyAddress',

            'GemFourMedia\GCompany\Components\GCompanyMap' => 'companyMap',
            'GemFourMedia\GCompany\Components\GCompanyShowCase' => 'companyShowcase',
            'GemFourMedia\GCompany\Components\GCompanyClient' => 'companyClient',
            
            'GemFourMedia\GCompany\Components\GCompanyArticleDetail' => 'companyArticle',
            'GemFourMedia\GCompany\Components\GCompanyArticleList' => 'companyArticles',
        ];

    }

    /**
     * Registers any form widgets implemented in this plugin.
     */
    public function registerFormWidgets()
    {
        return [
            'GemFourMedia\GCompany\FormWidgets\GAddressFinder' => [
                'label' => 'Address Finder',
                'code'  => 'gaddressfinder'
            ]
        ];
    }
}
