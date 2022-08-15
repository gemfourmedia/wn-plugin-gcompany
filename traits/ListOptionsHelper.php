<?php namespace GemFourMedia\GCompany\Traits;

use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

trait ListOptionsHelper {
    
    /**
     * List List of allowed showcase model of GCompany Showcase Components
     * @param string
     * @return array
     */
    public function getAllowedArticleShowCase()
    {
        return [
            'introduction' => trans('gemfourmedia.gcompany::lang.options.group.introduction'),
            'portfolio' => trans('gemfourmedia.gcompany::lang.options.group.portfolio'),
            'service' => trans('gemfourmedia.gcompany::lang.options.group.service'),
        ];
    }
    
    /**
     * List List of allowed showcase model of GCompany Showcase Components
     * @param string
     * @return array
     */
    public function getAllowedShowCase()
    {
        return [
            'testimonial' => trans('gemfourmedia.gcompany::lang.features.testimonial'),
            'client' => trans('gemfourmedia.gcompany::lang.features.client'),
            'partner' => trans('gemfourmedia.gcompany::lang.features.partner'),
            'file' => trans('gemfourmedia.gcompany::lang.features.file'),
            'member' => trans('gemfourmedia.gcompany::lang.features.member'),
        ];
    }

    public function listCompanyArticlesPage()
    {
        return $this->listGCompanyCmsPages('companyArticles');
    }

    public function listCompanyArticlePage()
    {
        return $this->listGCompanyCmsPages('companyArticle');
    }
    
    /**
     * List CMS pages which has specific GCompany components
     * @param string
     * @return array
     */
    public function listGCompanyCmsPages($componentName = '', $exceptPage='') {
        $theme = Theme::getActiveTheme();

        $pages = CmsPage::listInTheme($theme, true);
        $cmsPages = [];

        foreach ($pages as $page) {
            if (!$page->hasComponent($componentName)) {
                continue;
            }
            if ($exceptPage && $page->baseFileName == $exceptPage) {
                continue;
            }
            $cmsPages[$page->baseFileName] = $page->baseFileName;
        }
        return $cmsPages;
    }

}