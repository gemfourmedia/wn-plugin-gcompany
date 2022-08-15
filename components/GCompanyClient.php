<?php namespace GemFourMedia\GCompany\Components;

use Event;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use GemFourMedia\GCompany\Client;

class GCompanyClient extends ComponentBase
{
    use \GemFourMedia\GCompany\Traits\PageMetaHelper;

    public $cssClass;
    public $enableAssets;

    public $item;

    public $portfolioPage;
    public $testimonialPage;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.client.name',
            'description' => 'gemfourmedia.gcompany::lang.components.client.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.client.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.client.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ],
            'slug' => [
                'title' => 'gemfourmedia.gcompany::lang.components.client.props.slug',
                'type' => 'string',
                'default' => '{{:slug}}'
            ],
            'portfolioPage' => [
                'title'       => 'gemfourmedia.gcompany::lang.components.client.props.portfolioPage',
                'description' => 'gemfourmedia.gcompany::lang.components.client.props.portfolioPage_desc',
                'type'        => 'dropdown',
                'default'     => '',
                'showExternalParam' => false,
            ],
            'testimonialPage' => [
                'title'       => 'gemfourmedia.gcompany::lang.components.client.props.testimonialPage',
                'description' => 'gemfourmedia.gcompany::lang.components.client.props.testimonialPage_desc',
                'type'        => 'dropdown',
                'default'     => '',
                'showExternalParam' => false,
            ],
            'setPageMeta' => [
                'title'             => 'gemfourmedia.gcompany::lang.components.client.props.setPageMeta',
                'description'       => 'gemfourmedia.gcompany::lang.components.client.props.setPageMeta_desc',
                'type'              => 'checkbox',
                'default'           => true,
                'group'             => 'gemfourmedia.gcompany::lang.components.client.props.group_advanced',
                'showExternalParam' => false,
            ],
        ];
    }

    public function getPortfolioPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getTestimonialPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            if (isset($params['slug'])) {
                $records = Client::transWhere('slug', $params['slug'], $oldLocale)->first();
                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams['slug'] = $records['slug'];
                }
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->portfolioPage = $this->page['portfolioPage'] = $this->property('portfolioPage');
        $this->testimonialPage = $this->page['testimonialPage'] = $this->property('testimonialPage');

        $this->item = $this->page['item'] = $this->loadItem();
        $this->setPageMeta($this->item);

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
        $item = new Client;
        $query = $item->query();

        if ($item->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }

        $item = $query->with(['testimonials', 'portfolios'])->published()->first();

        return $item;
    }
}
