<?php namespace GemFourMedia\GCompany\Components;

use Cms\Classes\ComponentBase;
use GemFourMedia\GCompany\Models\Info;

class GCompanyInfo extends ComponentBase
{
    public $cssClass;
    public $title;
    public $subtitle;
    public $config = [];
    public $company;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcompany::lang.components.info.name',
            'description' => 'gemfourmedia.gcompany::lang.components.info.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcompany::lang.components.info.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcompany::lang.components.info.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcompany::lang.components.info.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'enableAssets' => [
                'title' => 'gemfourmedia.gcompany::lang.components.info.props.enableAssets',
                'type' => 'checkbox',
                'default' => false,
                'showExternalParam' => false,
            ]
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->loadCompanyInfo();
    }

    public function onRender()
    {
        if (!$this->company) {
            $this->prepareVars();
            $this->loadCompanyInfo();
        }
    }

    public function loadCompanyInfo()
    {
        $this->company = ($this->controller->vars['gcompany']) ? $this->controller->vars['gcompany'] : Info::instance();
    }

    public function prepareVars()
    {
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');
        $this->config = $this->page['config'] = $this->prepareConfig();

    }

    /*
     * Depreciated
     */
    protected function prepareConfig()
    {
        return [
            'full_name' => $this->property('full_name', true),
            'short_name' => $this->property('short_name', true),
            'address' => $this->property('address', true),
            'email' => $this->property('email', true),
            'hotline' => $this->property('hotline', true),
            'taxcode' => $this->property('taxcode', true),
            'business_license' => $this->property('business_license', true),
            'desc' => $this->property('desc', true),
            'logo' => $this->property('logo', true),
            'business_hour_short' => $this->property('business_hour_short', true),
            'business_hour_full' => $this->property('business_hour_full', true),
            'socials' => $this->property('socials', true),
            'banks' => $this->property('banks', true),
            'extrafields' => $this->property('extrafields', true),
            'mil' => $this->property('mil', true),
        ];
    }
}